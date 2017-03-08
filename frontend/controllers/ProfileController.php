<?php

namespace frontend\controllers;

use common\models\ForumMessages;
use common\models\ForumVotes;
use common\models\User;
use Yii;
use yii\web\Response;

class ProfileController extends \yii\web\Controller
{
    //todo кнопка "заблокировать пользователя"
    //todo кнопка "отправить пользователю оповещение"

    /**
     * Формирует страницу профиля пользователя
     *
     * @param null|integer $id
     * @return string
     */
    public function actionIndex($id = null)
    {
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

        $isBanned = false; //todo добавить проверку, заблокирован ли пользователь
        $isAbleToBanOrWrite = !Yii::$app->user->isGuest && !$isOwnProfile && (true /* todo проверка роли админа или модератора */);
        $isAdmin = false; // todo добавить отдельную проверку

        $ROLE_MAP = [
            'admin' => 'администратор',
            'moderator' => 'модератор',
            'user' => 'пользователь'
        ];

        $roleName = 'Пользователь'; // todo получать роль

        $renderingData = [
            'isOwnProfile' => $isOwnProfile,
            'userInfo' => $userInfo,
            'isOnline' => $userInfo->isOnline(),
            'isBanned' => $isBanned,
            'isAbleToBanOrWrite' => $isAbleToBanOrWrite,
            'isAdmin' => $isAdmin,
            'roleName' => $roleName
        ];

        if(!$isBanned && $isOwnProfile) {
            //Добавление к данным статистики пользователя
            //todo добавить кеширование статистики на некоторый срок
            $msg_stats = ForumMessages::getUserMsgCountAndRating($id);
            $created_themes_count = ForumMessages::getCreatedRootsByUserCount($id);
            $user_rates = ForumVotes::getUserMsgCountAndRating($id);

            $summary_msg_count = $msg_stats['msg_summary_count'];
            $user_rates_count = $user_rates['votes_count'];

            $renderingData['stats'] = [
                'created_messages_count' => $summary_msg_count,
                'average_user_rating' => (round($summary_msg_count != 0 ? $msg_stats['msg_summary_rating'] / $summary_msg_count : 0, 3)*100).'%',
                'created_themes_count' => $created_themes_count,
                'user_rates_count' => $user_rates_count,
                'summary_user_rate' => (round($user_rates_count != 0 ? $user_rates['votes_summary_rate'] / $user_rates_count : 0, 3)*100).'%',
            ];
        }

        return $this->render('index', $renderingData);
    }

    public function actionChangeLogin($newValue = null)
    {
        if(!Yii::$app->request->isAjax) return $this->redirect(['/profile']);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if(!$newValue) return ['result' => 'error', 'message' => 'Вы не отправили новый логин!'];
        if(Yii::$app->user->isGuest) return ['result' => 'error', 'message' => 'Вы не аутентифицированы!'];
        //todo проверка, находится ли пользователь в бан-листе
        //todo вынести в глобальные настройки мин. и макс. длину логина
        if(strlen($newValue) < 4) return ['result' => 'error', 'message' => 'Слишком короткий логин! Минимальная допустимая длина: 4 символа'];
        if(strlen($newValue) > 25) return ['result' => 'error', 'message' => 'Слишком длинный логин! Максимальная допустимая длина: 25 символов'];

        $userModel = User::findOne(['id' => Yii::$app->user->id]);

        if($userModel->username == $newValue) return ['result' => 'error', 'message' => 'Вы не изменили свой логин!'];

        $userModel->username = $newValue;
        if(!$userModel->save()) return ['result' => 'error', 'message' => 'Непредвиденная ошибка сохранения.'];

        return ['result' => 'ok', 'message' => "Ваш логин изменён на $newValue"];
    }

    public function actionChangeEmail($newValue = null)
    {
        if(!Yii::$app->request->isAjax) return $this->redirect(['/profile']);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if(!$newValue) return ['result' => 'error', 'message' => 'Вы не отправили новый email!'];
        if(Yii::$app->user->isGuest) return ['result' => 'error', 'message' => 'Вы не аутентифицированы!'];
        //todo проверка, находится ли пользователь в бан-листе
        if(strlen($newValue) < 3) return ['result' => 'error', 'message' => 'Слишком короткая почта! Минимальная допустимая длина: 3 символа'];
        if(strlen($newValue) > 125) return ['result' => 'error', 'message' => 'Слишком длинная почта! Максимальная допустимая длина: 125 символов'];

        $userModel = User::findOne(['id' => Yii::$app->user->id]);

        if($userModel->user_mail == $newValue) return ['result' => 'error', 'message' => 'Вы не изменили свой e-mail !'];

        $userModel->user_mail = $newValue;
        if(!$userModel->save()) return ['result' => 'error', 'message' => 'Непредвиденная ошибка сохранения.'];

        return ['result' => 'ok', 'message' => "Ваш e-mail изменён на $newValue"];
    }

    public function actionChangePassword($newValue = null)
    {
        if(!Yii::$app->request->isAjax) return $this->redirect(['/profile']);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if(!$newValue) return ['result' => 'error', 'message' => 'Вы не отправили новый пароль!'];
        if(Yii::$app->user->isGuest) return ['result' => 'error', 'message' => 'Вы не аутентифицированы!'];
        //todo проверка, находится ли пользователь в бан-листе
        if(strlen($newValue) < 4) return ['result' => 'error', 'message' => 'Слишком короткий пароль! Минимальная допустимая длина: 4 символа'];
        if(strlen($newValue) > 100) return ['result' => 'error', 'message' => 'Слишком длинная почта! Максимальная допустимая длина: 100 символов'];

        $userModel = User::findOne(['id' => Yii::$app->user->id]);
        $userModel->setPassword($newValue);
        if(!$userModel->save()) return ['result' => 'error', 'message' => 'Непредвиденная ошибка сохранения.'];

        return ['result' => 'ok', 'message' => "Ваш пароль успешно изменён."];
    }

    public function actionChangeFirstName($newValue = null)
    {
        if(!Yii::$app->request->isAjax) return $this->redirect(['/profile']);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if(!$newValue) return ['result' => 'error', 'message' => 'Вы не отправили новое имя!'];
        if(Yii::$app->user->isGuest) return ['result' => 'error', 'message' => 'Вы не аутентифицированы!'];
        //todo проверка, находится ли пользователь в бан-листе
        if(strlen($newValue) < 4) return ['result' => 'error', 'message' => 'Слишком короткое имя! Минимальная допустимая длина: 4 символа'];
        if(strlen($newValue) > 60) return ['result' => 'error', 'message' => 'Слишком длинное имя! Максимальная допустимая длина: 60 символов'];

        $userModel = User::findOne(['id' => Yii::$app->user->id]);
        if($userModel->real_name == $newValue) return ['result' => 'error', 'message' => 'Вы не изменили своё имя.'];
        $userModel->real_name = $newValue;
        if(!$userModel->save()) return ['result' => 'error', 'message' => 'Непредвиденная ошибка сохранения.'];

        return ['result' => 'ok', 'message' => "Ваше имя успешно изменено."];
    }

    public function actionChangeSecondName($newValue = null)
    {
        if(!Yii::$app->request->isAjax) return $this->redirect(['/profile']);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if(!$newValue) return ['result' => 'error', 'message' => 'Вы не отправили новую фамилию!'];
        if(Yii::$app->user->isGuest) return ['result' => 'error', 'message' => 'Вы не аутентифицированы!'];
        //todo проверка, находится ли пользователь в бан-листе
        if(strlen($newValue) < 4) return ['result' => 'error', 'message' => 'Слишком короткая фамилия! Минимальная допустимая длина: 4 символа'];
        if(strlen($newValue) > 100) return ['result' => 'error', 'message' => 'Слишком длинная фамилия! Максимальная допустимая длина: 100 символов'];

        $userModel = User::findOne(['id' => Yii::$app->user->id]);
        if($userModel->real_surname == $newValue) return ['result' => 'error', 'message' => 'Вы не изменили свою фамилию.'];
        $userModel->real_surname = $newValue;
        if(!$userModel->save()) return ['result' => 'error', 'message' => 'Непредвиденная ошибка сохранения.'];

        return ['result' => 'ok', 'message' => "Ваша фамилия успешно изменёна."];
    }
}
