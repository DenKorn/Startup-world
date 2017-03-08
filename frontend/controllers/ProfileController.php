<?php

namespace frontend\controllers;

use common\models\ForumMessages;
use common\models\ForumVotes;
use common\models\User;
use Yii;

class ProfileController extends \yii\web\Controller
{

    public function actionIndex($id = null)
    {
        //todo сделать поправки на полномочия модератора:
        //todo кнопка "заблокировать пользователя"
        //todo кнопка "отправить пользователю оповещение"

        // определяем, свой ли профиль загрузил пользователь (тогда ему предоставлятся больше инфы) и другой интерфейс
        $isOwnProfile = false;

        if(!$id) {
            //если пользователь незалогинен, и id не передан - делаем перенаправление на страницу входа
            if(Yii::$app->user->isGuest) {
                return $this->redirect(['site/login']);
            }
            $id = Yii::$app->user->id;
            $isOwnProfile = true;
        } else {
            if($id == Yii::$app->user->id) {
                $isOwnProfile = true;
            }
        }


        $userInfo = User::findOne(['id' => $id]);

        //Если запрашиваемый пользователь не найден - выводим сообщение об этом
        if(!$userInfo) {
            return $this->render('not_found', ['requestedId' => $id]);
        }

        //todo добавить проверку, заблокирован ли пользователь
        $renderingData = [
            'isOwnProfile' => $isOwnProfile,
            'userInfo' => $userInfo,
            'isOnline' => $userInfo->isOnline(),
        ];

        if($isOwnProfile) {
            //todo передача статистики: всего сообщений, всего оценок, суммарный рейтинг
            $msg_stats = ForumMessages::getUserMsgCountAndRating($id);
            $created_themes_count = ForumMessages::getCreatedRootsByUserCount($id);
            $user_rates = ForumVotes::getUserMsgCountAndRating($id);

            $summary_msg_count = $msg_stats['msg_summary_count'];
            $user_rates_count = $user_rates['votes_count'];

            $renderingData['stats'] = [
                'created_messages_count' => $summary_msg_count,
                'average_user_rating' => round($summary_msg_count != 0 ? $msg_stats['msg_summary_rating'] / $summary_msg_count : 0, 3),
                'created_themes_count' => $created_themes_count,
                'user_rates_count' => $user_rates_count,
                'summary_user_rate' => round($user_rates_count != 0 ? $user_rates['votes_summary_rate'] / $user_rates_count : 0, 3)
            ];
        }

        return $this->render('index', $renderingData);

    }

}
