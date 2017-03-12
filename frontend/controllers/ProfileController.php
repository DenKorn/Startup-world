<?php

namespace frontend\controllers;

use common\models\ForumBanList;
use common\models\ForumMessages;
use common\models\ForumNotifications;
use common\models\ForumVotes;
use common\models\User;
use Yii;
use yii\web\Response;

class ProfileController extends \yii\web\Controller
{

    /**
     * Блокирует лишь разблокирует пользователя в зависимости от второго аргумента
     * 1 - блокировать, 2 - разблокировать
     *
     * @param $user_id integer
     * @param $isBlock integer
     * @return array|Response
     */
    public function actionBlockUser($user_id, $isBlock, $reason = "")
    {
        if(!Yii::$app->request->isAjax)
            return $this->redirect(['/profile']);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if(Yii::$app->user->isGuest) return ['result' => 'error', 'message' => 'Вы не авторизованы!'];
        $initiatorUserModel = ForumBanList::findOne(['user_id' => Yii::$app->user->id]);
        if(!Yii::$app->user->can('moderator')) return ['result' => 'error', 'message' => 'Вы не владеете полномочиями для блокировки пользователя!'];
        if($initiatorUserModel) return ['result' => 'error', 'message' => 'Вы не можете блокировать пользователя, будучи сами заблокированным.'];
        if($user_id == Yii::$app->user->id) return ['result' => 'error', 'message' => 'Нельзя блокировать/разюлокировать самого себя!'];

        $banRecord = ForumBanList::findOne(['user_id' => $user_id]);

        $reloadMessage = '{ "command" : "reload", "parameter" : 5000 }';

        switch ($isBlock) {
            case 1:
                if($banRecord) return ['result' => 'error', 'message' => 'Пользователь уже заблокирован!'];
                $banRecord = new ForumBanList(['user_id' => $user_id, 'reason' => $reason]);
                if(!$banRecord->save()) return ['result' => 'error', 'message' => 'Не удалось заблокировать пользователя!'];

                $notification = new ForumNotifications(['recipient_id' => $user_id, 'type' => 'warning', 'message' => 'Вас забанили'.(strlen($reason) > 2 ? " за: ".$reason : "")]);
                $notification->save();

                $notification = new ForumNotifications(['recipient_id' => $user_id, 'type' => 'system', 'message' => $reloadMessage]);
                $notification->save();

                return ['result' => 'ok', 'message' => 'Пользователь заблокирован.'];

            case 2:
                if(!$banRecord) return ['result' => 'error', 'message' => 'Пользователь не был заблокирован!'];
                if(!$banRecord->delete()) return ['result' => 'error', 'message' => 'Не удалось разблокировать пользователя!'];

                $notification = new ForumNotifications(['recipient_id' => $user_id, 'type' => 'alert', 'message' => 'Вас разбанили, удачного общения!']);
                $notification->save();

                $notification = new ForumNotifications(['recipient_id' => $user_id, 'type' => 'system', 'message' => $reloadMessage]);
                $notification->save();

                return ['result' => 'ok', 'message' => 'Пользователь разблокирован.'];
        }
        return null;
    }

    /**
     * Отправляет пользователю уведомление
     *
     * @param $user_id integer
     * @param $message string
     * @return array|Response
     */
    public function actionNotifyUser($user_id, $message)
    {
        if(!Yii::$app->request->isAjax)
            return $this->redirect(['/profile']);

        Yii::$app->response->format = Response::FORMAT_JSON;
        if(Yii::$app->user->isGuest) return ['result' => 'error', 'message' => 'Вы не авторизованы!'];
        if(! Yii::$app->user->can('moderator')) return ['result' => 'error', 'message' => 'Вы не обладаете полномочиями для отправки уведомлений!'];
        if(ForumBanList::findOne(['user_id' => Yii::$app->user->id])) return ['result' => 'error', 'message' => 'Вы заблокированы и не можете отправлять уведомленения!!'];

        if($user_id == Yii::$app->user->id) return ['result' => 'error', 'message' => 'Нельзя отправлять уведомления самому себе!'];

        $msg_length = strlen($message);
        if($msg_length < 1) return ['result' => 'error', 'message' => 'Минимальная длина уведомления 1 символ!'];
        if($msg_length > 255) return ['result' => 'error', 'message' => 'Максимальная длина уведомления 255 символов!'];

        $notification = new ForumNotifications(['recipient_id' => $user_id, 'type' => 'alert', 'message' => $message]);
        if(!$notification->save()) return ['result' => 'error', 'message' => 'Ошибка отправки уведомления пользователю!'];

        return ['result' => 'ok', 'message' => 'Уведомление успешно отправлено'];
    }

    /**
     * Формирует страницу профиля пользователacя
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

        $bannedRecord = ForumBanList::findOne(['user_id' => $id]);
        $isBanned = $bannedRecord ? true : false;
        $isAbleToBanOrWrite = Yii::$app->user->can('moderator');
        $isAdmin = Yii::$app->user->can('admin');

        $targetUserRoles = Yii::$app->authManager->getRolesByUser($id);

        $renderingData = [
            'targetUserRoles' => $targetUserRoles,
            'isOwnProfile' => $isOwnProfile,
            'userInfo' => $userInfo,
            'isOnline' => $userInfo->isOnline(),
            'isBanned' => $isBanned,
            'isAbleToBanOrWrite' => $isAbleToBanOrWrite,
            'isAdmin' => $isAdmin,
            'banReason' => ( $bannedRecord && $bannedRecord->reason ? $bannedRecord->reason : "")
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
        if(strlen($newValue) < 3) return ['result' => 'error', 'message' => 'Слишком короткий логин! Минимальная допустимая длина: 3 символа'];
        if(strlen($newValue) > 25) return ['result' => 'error', 'message' => 'Слишком длинный логин! Максимальная допустимая длина: 25 символов'];

        $userModel = Yii::$app->user->identity;

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

        $userModel = Yii::$app->user->identity;

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

        $userModel = Yii::$app->user->identity;
        $userModel->setPassword($newValue);
        if(!$userModel->save()) return ['result' => 'error', 'message' => 'Непредвиденная ошибка сохранения.'];

        return ['result' => 'ok', 'message' => "Ваш пароль успешно изменён."];
    }

    public function actionChangeFirstName($newValue = null)
    {
        if(!Yii::$app->request->isAjax) return $this->redirect(['/profile']);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if(!$newValue)
            return ['result' => 'error', 'message' => 'Вы не отправили новое имя!'];
        if(Yii::$app->user->isGuest)
            return ['result' => 'error', 'message' => 'Вы не аутентифицированы!'];
        if(ForumBanList::findOne(['user_id' => Yii::$app->user->id]))
            return ['result' => 'error', 'message' => 'Вы не можете изменить своё имя, так как вы заблокированы!'];
        if(strlen($newValue) < 4)
            return ['result' => 'error', 'message' => 'Слишком короткое имя! Минимальная допустимая длина: 4 символа'];
        if(strlen($newValue) > 60)
            return ['result' => 'error', 'message' => 'Слишком длинное имя! Максимальная допустимая длина: 60 символов'];

        $userModel = Yii::$app->user->identity;
        if($userModel->real_name == $newValue)
            return ['result' => 'error', 'message' => 'Вы не изменили своё имя.'];
        $userModel->real_name = $newValue;
        if(!$userModel->save())
            return ['result' => 'error', 'message' => 'Непредвиденная ошибка сохранения.'];

        return ['result' => 'ok', 'message' => "Ваше имя успешно изменено."];
    }

    public function actionChangeSecondName($newValue = null)
    {
        if(!Yii::$app->request->isAjax)
            return $this->redirect(['/profile']);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if(!$newValue)
            return ['result' => 'error', 'message' => 'Вы не отправили новую фамилию!'];
        if(Yii::$app->user->isGuest)
            return ['result' => 'error', 'message' => 'Вы не аутентифицированы!'];
        if(ForumBanList::findOne(['user_id' => Yii::$app->user->id]))
            return ['result' => 'error', 'message' => 'Вы не можете изменить свою фамилию, так как вы заблокированы!'];
        if(strlen($newValue) < 4)
            return ['result' => 'error', 'message' => 'Слишком короткая фамилия! Минимальная допустимая длина: 4 символа'];
        if(strlen($newValue) > 100)
            return ['result' => 'error', 'message' => 'Слишком длинная фамилия! Максимальная допустимая длина: 100 символов'];

        $userModel = Yii::$app->user->identity;
        if($userModel->real_surname == $newValue)
            return ['result' => 'error', 'message' => 'Вы не изменили свою фамилию.'];
        $userModel->real_surname = $newValue;
        if(!$userModel->save())
            return ['result' => 'error', 'message' => 'Непредвиденная ошибка сохранения.'];

        return ['result' => 'ok', 'message' => "Ваша фамилия успешно изменёна."];
    }

    public function actionSetUserRole($user_id, $new_role)
    {
        if(!Yii::$app->request->isAjax)
            return $this->redirect(['/profile']);
        Yii::$app->response->format = Response::FORMAT_JSON;

        if(!Yii::$app->user->can('admin'))
            return ['result' => 'error', 'message' => 'Вы не обладаете полномочиями администратора для совершения этого действия!'];

        if(ForumBanList::findOne(['user_id' => Yii::$app->user->id]))
            return ['result' => 'error', 'message' => 'Вы не можете ничего сделать, так как вы заблокированы!'];

        if(!User::findOne(['id' => $user_id]))
            return ['result' => 'error', 'message' => 'Целевой пользователь не найден!'];

        if(Yii::$app->user->id == $user_id)
            return ['result' => 'error', 'message' => 'Вы не можете меня собственную роль!'];

        if($new_role != 'admin' && $new_role != 'user' && $new_role != 'moderator')
            return ['result' => 'error', 'message' => "Неизвестное название роли $new_role !"];

        Yii::$app->authManager->revokeAll($user_id);
        Yii::$app->authManager->assign(Yii::$app->authManager->getRole($new_role),$user_id);

        $NEW_ROLE_MAP = [
            'admin' => 'администратора',
            'moderator' => 'модератора',
            'user' => 'обычного пользователя'
        ];

        $notification = new ForumNotifications(['recipient_id' => $user_id, 'type' => 'alert', 'message' => "Ваша роль изменена на $NEW_ROLE_MAP[$new_role]"]);
        $notification->save();

        return ['result' => 'ok', 'message' => "Роль пользователя успешно изменена на $NEW_ROLE_MAP[$new_role]!"];
    }
}
