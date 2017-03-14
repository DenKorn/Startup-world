<?php
/**
 * Содержит консольные команды для форума, для которых есть смысл быть реализованными через
 * cron или люобой другой планировщик задач
 */

namespace console\controllers;

use common\models\GeneralSettings;
use Yii;
use yii\console\Controller;
use yii\db\Query;
use yii\helpers\Url;

class ForumTasksController extends Controller
{
    /**
     * Формирует строку содержимого отправляемого письма
     *
     * @param $notifications array
     * @return string
     */
    private function getFormedMailBody($notifications)
    {//todo по разному обозначить сообщения типа alert и warning
        $mail_only = $notifications[0]['mail_only'];
        $username = $notifications[0]['username'];
        $notificationsText = "<ul>";
        foreach ($notifications as $notifyMsg) {
            $notificationsText .= "<li>".$notifyMsg['message']."</li>";
        }
        $notificationsText .= "</ul>";

        return "<p>Уважаемый $username !</p><br>"
            .($mail_only ? "<p>".$notifications[0]['message']."</p>" : "Пока вы отсутствовали, вам пришло "
                .(count($notifications) == 1 ? "уведомление:" : "несколько уведомлений:")
                ."<p>$notificationsText</p>"
            )
            ."<br><p>С уважением,<br>администрация Startup World</p>";
    }

    /**
     * Удаляет старые системные сообщения. Критерий возраста берется из глобальных настроек
     * @param $NOTIFICATIONS_SETTINGS object
     */
    private function removeIrrelevantNotifications($NOTIFICATIONS_SETTINGS)
    {
        $max_sys_notif_age_minutes = ($NOTIFICATIONS_SETTINGS->online_interval_in_seconds + $NOTIFICATIONS_SETTINGS->online_interval_dispersion)/6;
        $days_to_notify = $NOTIFICATIONS_SETTINGS->max_branch_age_to_notify_in_days;

        Yii::$app->db->createCommand("
          DELETE FROM forum_notifications
          WHERE 
          (type = 'system' AND DATE_SUB(CURRENT_TIMESTAMP, INTERVAL $max_sys_notif_age_minutes MINUTE) > sended_at)
          OR
          ((type = 'alert' OR type = 'warning') AND DATE_SUB(CURRENT_TIMESTAMP, INTERVAL $days_to_notify DAY) > sended_at);
        ")->execute();
    }

    /**
     * Принимает на вход обьект настроек уведомлений для получения уведомлений определенной длительности.
     * Выдаёт массив подходящих для отображения уведомлений.
     *
     * @param $NOTIFICATIONS_SETTINGS object
     * @return array
     */
    private function getNotificationsToMail($NOTIFICATIONS_SETTINGS)
    {
        $oldPeriod = $NOTIFICATIONS_SETTINGS->min_notify_age_for_mailing_in_hours;

        return Yii::$app->db->createCommand("
        SELECT
          user.username,
          user.real_name,
          user.real_surname,
          user.user_mail,
          notifications.recipient_id,
          notifications.type,
          notifications.message,
          notifications.sended_at,
          notifications.mail_only
        FROM
          (SELECT * FROM
             forum_notifications
           WHERE (DATE_SUB(CURRENT_TIMESTAMP, INTERVAL $oldPeriod HOUR) > sended_at)
          ) AS notifications
          INNER JOIN
          (SELECT * FROM
            user
          WHERE user_mail != ''
          ) as user
            ON (notifications.recipient_id = user.id);
        ")->queryAll();
    }

    /**
     * Проверяет таблицу forum_notifications на предмет устаревших уведомлений и уведомлений только для почты.
     * Уведомления только для почты отправляются.
     * Обычные уведомления для каждого пользователя группируются в одном письме и отправляются.
     * Системные уведомл. игнорируются.
     * Все уведомления, прошедшие проверку, удаляются.
     *
     * @return int
     */
    public function actionCheckOldNotifications()
    {
        $NOTIFICATIONS_SETTINGS = GeneralSettings::getSettingsObjByName('USER_NOTIFICATIONS');
        // удаление неактуальных сообщений
        $this->removeIrrelevantNotifications($NOTIFICATIONS_SETTINGS);

        // получим список уведомлений
        $toMail = $this->getNotificationsToMail($NOTIFICATIONS_SETTINGS);
        //Если полученный массив оповещений к отправке пуст, то нечего удалять и отправлять, можно завершать функцию
        if (count($toMail) == 0) return 0;

        // иначе, полученный список уведомлений отсортируем по recipient_id пользователей, и
        // по пути отбрасывая пользователей с пустым полем user_mail, создадим набор групп содержимого для писем
        // каждому пользователю
        usort($toMail, function($a, $b) {
            if($a['recipient_id'] == $b['recipient_id']) return 0;
            return ($a['recipient_id'] < $b['recipient_id']) ? -1 : 1;
        });

        $to_send = [];
        $mailGroups = [];

        foreach ($toMail as $mail) {
            if($mail['mail_only']) {
                //одиночные письма можно сразу добавлять в очередь на отправку
                 $to_send[] = Yii::$app->mailer->compose('default-message' ,[
                     'msgContent' => $this->getFormedMailBody([$mail])
                 ])  ->setFrom('topscrumboard@gmail.com')
                     ->setTo($mail['user_mail'])
                     ->setSubject('Рассылка Startup Forum');
            } else {
                if(!array_key_exists($mail['username'], $mailGroups)) {
                    $mailGroups[$mail['username']] = [];
                }
                $mailGroups[$mail['username']][] = $mail;
            }
        }

        // а теперь просто перебираем сформированные в группы уведомления и добавляем в очередь отправки писем
        foreach ($mailGroups as $group) {
            $to_send[] = Yii::$app->mailer->compose('default-message' ,[
                'msgContent' => $this->getFormedMailBody($group)
            ])  ->setFrom('topscrumboard@gmail.com')
                ->setTo($group[0]['user_mail'])
                ->setSubject('Ваши уведомления');
        }

        Yii::$app->mailer->sendMultiple($to_send);

        $this->removeSendedNotifications($NOTIFICATIONS_SETTINGS);

        return 0;
    }

    private function removeSendedNotifications($NOTIFICATIONS_SETTINGS)
    {
        $oldPeriod = $NOTIFICATIONS_SETTINGS->min_notify_age_for_mailing_in_hours;

        Yii::$app->db->createCommand("
        DELETE FROM forum_notifications
        WHERE (DATE_SUB(CURRENT_TIMESTAMP, INTERVAL $oldPeriod HOUR) > sended_at);
        ")->execute();
    }

    public function actionIndex()
    {
        echo 'Type forum-tasks/check-old-notifications to start notifications checking out. Or type help to see some extra info.';
    }

    public function actionHelp()
    {
        echo "
*----------------------------------------------------------*
| Now this command have only two subcommands:              |
|                                                          |
|     1) help                                              |
|     2) check-old-notiications                            |
|                                                          |
*----------------------------------------------------------*
 Yes, help na angliiskom, ibo kyryllitsa doesn't work :(
 ";
    }
}