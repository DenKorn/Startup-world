<?php

namespace frontend\controllers;

use common\models\ForumRoots;
use common\models\ForumVotes;
use common\models\GeneralSettings;
use common\models\User;
use DateTime;
use Yii;
use common\models\ForumMessages;
use yii\helpers\Url;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;

class DiscussionsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'], //todo дообавить остальные действия
                ],
            ],
        ];
    }

    /**
     * Отображение страницы с перепиской в теме, рендеринг сообщений происходит на клиенте.
     * ToDo в случае отсутствия записей о теме форума бросать exception
     * @param $id int
     * @return mixed
     */
    public function actionIndex($id)
    {
        //проверка наличия запраиваемой темы форума (корневого сообщения):
        $rootMsgModel = ForumMessages::findOne(['root_theme_id' => $id]);
        if(!$rootMsgModel) {
            return $this->render('error',[
                'name' => 'Страница не найдена',
                'message' => 'Сожалеем, но тема форума не найдена. 
            Возможно, вы перешли по старой или ошибочной ссылке. Ну или БД форума поломалась к чертям :)'
            ]);
        }

        //проверка существования автора темы форума
        $theme_author = User::findOne($rootMsgModel->user_id);
            if(!$theme_author) {
                return $this->render('error',[
                    'name' => 'Ошибка загрузки',
                    'message' => 'Не удалось загрузить содержимое форума. Автор темы не существует в базе сайта.
                 Ну или БД форума слетела к чертям :)'
                ]);
            }

        $clientModel = User::findOne(Yii::$app->user->id);
        $apiBaseUrl = Url::home(true).'/discussions/';

        return $this->render('index', [
            'apiBaseUrl' => $apiBaseUrl,
            'rootMsgId' => $rootMsgModel->id,
            'discussionTitle' => ForumRoots::findOne($id)->title,
            'discussionInitiatorUsername' => $theme_author->username,
            'rootMsgModel' => $rootMsgModel->getTreeStruct(0),
            'clientModel' => $clientModel,
        ]);
    }

    /**
     * Возвращает подветку сообщений в виде JSON-строки
     * @param $id
     * @param int $levels
     * @return null|\stdClass|Response
     */
    public function actionAjaxLoadBranch($id, $levels = 3)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if(Yii::$app->request->isAjax) {
            $model = ForumMessages::findOne($id);
            return $model->getTreeStruct($levels);
        } else return $this->redirect(['index', 'id' => $id]);
    }

    /**
     * Создание нового сообщения на форуме в ответ на одно какое-либо из созданных
     *
     * @param $respond_to int
     * @return string
     */
     public function actionCreateMessage($respond_to, $content)
    {
        //todo выполнять только по post
        //todo позже добавить проверку, не находится ли залогиненный пользователь в бан-листе
        //todo добавить контроль цензуры перед сохранением

        if(!Yii::$app->request->isAjax) return $this->redirect(['forum/index']);

        Yii::$app->response->format = Response::FORMAT_JSON;

        //проверка того, что пользователь залогинен:
        if(!Yii::$app->user->id) return ['result' => 'error', 'code' => 7, 'message' => 'Невозможно отправить сообщение: ваш аккаунт заблокирован или не существует.'];

        //проверка существования сообщения, на которое пытаемся написать ответ:
        if(!ForumMessages::findOne($respond_to)) return ['result' => 'error', 'code' => 1, 'message' => 'Целевое сообщение для ответа не найдено.'];

        $MSG_LIMITS = GeneralSettings::getSettingsObjByName('MESSAGES_LIMITS');

        //проверка длины текста сообщения, слишком длинное и слишком короткое сообщение отказываемся сохранять:
        $msgLength = strlen($content);
        if($msgLength > $MSG_LIMITS->max_message_length) return ['result' => 'error', 'code' => 2, 'message' => 'Превышена допустимая длина сообщения.'];
        if($msgLength < $MSG_LIMITS->min_message_length) return ['result' => 'error', 'code' => 6, 'message' => 'Отправленое сообщение слишком короткое.'];

        //Пробуем создать запись сообщения и сохранить её в БД:
        $newMsg = new ForumMessages(['content' => $content, 'parent_message_id' => $respond_to, 'user_id' => Yii::$app->user->id]);
        if (!$newMsg->save()) return ['result' => 'error', 'code' => 3, 'message' => 'Не удалось сохранить сообщение в базу данных.'];

        return [
            'result' => 'ok',
            'new_msg_id' => $newMsg->id,
            'created_at' => $newMsg->created_at,
            'msg_content' => $newMsg->content
        ];
    }

    /**
     * Обновление сообщения пользователя
     * Происходит лишь при условии, что это личное сообщение пользователя и оно не слишком давно оставлено, либо если доступ
     * пытается получить админ или модератор
     * @param integer $id
     * @param string $content
     * @return mixed
     */
    public function actionUpdateMessage($id, $content)
    {
        //todo сделать поправку на текущий часовой пояс при сравнении (в базу пишется время по Гринвичу)
        //todo максимальное количество прошедших часов для редактирования подтягивать из таблицы глобальных настроек, пока что это будет 5 часов
        //todo разрешить только через post
        //todo добавить цензурирование
        //todo позже добавить проверку, не находится ли залогиненный пользователь в бан-листе

        if(!Yii::$app->request->isAjax) return $this->redirect(['forum/index']);
        Yii::$app->response->format = Response::FORMAT_JSON;

        //проверка того, что пользователь залогинен:
        if(!Yii::$app->user->id) return ['result' => 'error', 'code' => 7, 'message' => 'Невозможно отправить сообщение: ваш аккаунт заблокирован или не существует.'];

        //проверка существования сообщения, которое пытаемся обновить:
        $targetMessage = ForumMessages::findOne($id);
        if(! $targetMessage) return ['result' => 'error', 'code' => 1, 'message' => 'Целевое сообщение для обновления не найдено.'];

        //проверка принадлежности рекактируемого сообщения автору:
        if($targetMessage->user_id != Yii::$app->user->id) return ['result' => 'error', 'code' => 2, 'message' => 'Вы пытаетесь редактировать чужое сообщение.'];

        $MSG_LIMITS = GeneralSettings::getSettingsObjByName('MESSAGES_LIMITS');

        //проверка срока давности сообщения с момента создания
        $time_now = new DateTime();
        $time_created_at = new DateTime($targetMessage->created_at);
        $interval = $time_now->diff($time_created_at,true)->h;
        if($interval > $MSG_LIMITS->still_editable_during_hours) return ['result' => 'error', 'code' => 3, 'message' => 'Истек допустимый срок для редактируемого сообщения.'];

        //проверка длины текста, слишком длинное или слишком короткое сообщение отказываемся сохранять
        $msgLength = strlen($content);
        if($msgLength > $MSG_LIMITS->max_message_length) return ['result' => 'error', 'code' => 4, 'message' => 'Превышена допустимая длина сообщения.'];
        if($msgLength < $MSG_LIMITS->min_message_length) return ['result' => 'error', 'code' => 6, 'message' => 'Обновляемое сообщение слишком короткое.'];

        $targetMessage->content = $content;
        if (! $targetMessage->save()) return ['result' => 'error', 'code' => 5, 'message' => 'Не удалось обновить сообщение.'];

        //Если сообщение таки сохранилось - сообщаем об этом
        return ['result' => 'ok', 'new_content' => $content];
    }

    /**
     * Удаление существующего принадлежащего пользователю сообщения, с непросроченным сроком давности с момента создания.
     * (правило срока давности не касается админов или модераторов)
     * @param integer $id
     * @return mixed
     */
    public function actionDeleteMessage($id)
    {
        // todo добавить в правилах разрешение выполнять эту функцию только через метод post
        // todo позже добавить проверку, не находится ли залогиненный пользователь в бан-листе
        // todo проверка наличия пользователя в бан-листе

        if(!Yii::$app->request->isAjax) return $this->redirect(['forum/index']);

        Yii::$app->response->format = Response::FORMAT_JSON;
        $targetMessage = ForumMessages::findOne($id);

        //проверка существования сообщения, которое пытаемся удалить
        if(!$targetMessage) return ['result' => 'error', 'code' => 1, 'message' => 'Целевое сообщение для удаления не найдено.'];

        //проверка того, что пользователь аутентифицирован
        if(!Yii::$app->user->id) return ['result' => 'error', 'code' => 2, 'message' => 'Невозможно удалить сообщение: ваш аккаунт или не существует.'];

        //проверка принадлежности удаляемого сообщения автору
        //todo поправку на доступ модератора
        if($targetMessage->user_id != Yii::$app->user->id)
            return ['result' => 'error', 'code' => 7, 'message' => 'Вы пытаетесь удалить чужое сообщение.'];

        $time_now = new DateTime();
        $time_created_at = new DateTime($targetMessage->created_at);
        $interval = $time_now->diff($time_created_at,true)->h;
        $MSG_LIMITS = GeneralSettings::getSettingsObjByName('MESSAGES_LIMITS');

        //проверка срока давности с момента создания
        if($interval > $MSG_LIMITS->still_editable_during_hours)
            return ['result' => 'error', 'code' => 3, 'message' => 'Истек допустимый срок для удаления этого сообщения.'];

        if(!$targetMessage->delete()) return ['result' => 'error', 'code' => 4, 'message' => 'Не удалось удалить сообщение.'];

        return ['result' => 'ok'];
    }

    /**
     * реализует голосование (лайк, убрать лайк, дизлайк, убрать дизлайк
     * @param $msg_id
     * @param string $action = {set,cancel}
     * @param integer $value = {-1 - dislike, 0 - nothing, 1 - like}
     * @return array|Response
     */
    public function actionUpdateVoting($msg_id, $action, $value = 0, $user)
    {
        if(!Yii::$app->request->isAjax) {
        //    return $this->redirect(['index']);
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        if(abs($value) > 1 || $value == 0 /* || !Yii::$app->user */) { //todo раскомментить шалуна
            return ['result' => 'error', 'message' => 'Не играйся с API, шалун.)'];
        }

        switch ($action) {
            case "set" : {
                //поиск уже поставленной оценки
                //если поставлена такая же оценка - то выдать сообщение об ошибке

                $settedVote = ForumVotes::findOne(['msg_id' => $msg_id, 'user_id' => $user]);
                //$settedVote = ForumVotes::findOne(['msg_id' => $msg_id, 'user_id' => Yii::$app->user->id]);
                if($settedVote) {
                    if($settedVote->value == $value) {
                        return ['result' => 'error', 'message' => 'Вы уже ставили оценку. Вы можете изменить её на '.($value == 1 ? 'дизлайк' : 'лайк')];
                    }
                    $settedVote->value = $value;
                    //если поставлена другая оценка - изменить её и вернуть соббщение об успехе
                    $settedVote->save();
                }
                $settedVote = new ForumVotes(['msg_id' => $msg_id, 'user_id' => $user, 'value' => $value]);
                //$settedVote = new ForumVotes(['msg_id' => $msg_id, 'user_id' => Yii::$app->user->id, 'value' => $value]);
                $settedVote->save();

                return ['result' => 'success', 'message' => 'Вы '.($value == 1 ? 'подняли' : 'понизили').' рейтинг сообщения.'];
                break;
            }
            case "cancel" : {
                        //если оценка найдена - удалить её и вернуть положительный результат
                        //если не найдена - сообщение об ошибке
                        break;
                    }
            }

    }
}
