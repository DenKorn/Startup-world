<?php

namespace frontend\controllers;

use common\models\ForumRoots;
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
     * @param $id
     * @return mixed
     */
    public function actionIndex($id)
    {
        $apiBaseUrl = Url::home(true).'/discussions/';
        $rootMsgModel = ForumMessages::find()->where(['root_theme_id' => $id])->one();
        if($rootMsgModel) {
            $theme_author = User::findOne($rootMsgModel->user_id);
            if($theme_author) {
                $clientModel = User::findOne(Yii::$app->user->id);
                return $this->render('index', [
                    'apiBaseUrl' => $apiBaseUrl,
                    'rootMsgId' => $rootMsgModel->id,
                    'discussionTitle' => ForumRoots::findOne($id)->title,
                    'discussionInitiatorUsername' => $theme_author->username,
                    'rootMsgModel' => $rootMsgModel->getTreeStruct(0),
                    'clientModel' => $clientModel,
                ]);
            } else return $this->render('error',[
                'name' => 'Ошибка загрузки',
                'message' => 'Не удалось загрузить содержимое форума. Автор темы не существует в базе сайта.
                 Ну или БД форума слетела к чертям :)'
            ]);

        } else return $this->render('error',[
            'name' => 'Страница не найдена',
            'message' => 'Сожалеем, но такой форум в БД не найден. 
            Возможно, вы перешли по старой или ошибочной ссылке. Ну или БД форума слетела к чертям :)'
        ]);
    }

    /**
     * Возвращает подветку сообщений в виде JSON
     * @param $id
     * @param int $levels
     * @return null|\stdClass|Response
     */
    public function actionAjaxLoadBranch($id, $levels = 3)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
       // if(Yii::$app->request->isAjax) {
            $model = ForumMessages::findOne($id);
            return $model->getTreeStruct($levels);
        //} else return $this->redirect(['index', 'id' => $id]);
    }

    /**
     * Создание нового сообщения на форуме в ответ на одно какое-либо из созданных
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string
     */
     public function actionCreateMessage($respond_to, $content)
    {
        $MAX_MESSAGE_LENGTH = 1500;
        $MIN_MESSAGE_LENGTH = 1;
        //todo max messages length затем подтягивать из добавленной таблицы настроек!
        //todo выполнять только по post

        Yii::$app->response->format = Response::FORMAT_JSON;
        if(Yii::$app->request->isAjax) {
            if(Yii::$app->user->id) { //проверка того, что пользователь залогинен todo позже добавить проверку, не находится ли залогиненный пользователь в бан-листе
                if(ForumMessages::findOne($respond_to)){ //проверка существования сообщения, на которое пытаемся написать ответ
                    if(strlen($content) <= $MAX_MESSAGE_LENGTH) { //проверка длины текста, слишком длинное сообщение отказываемся сохранять
                        if(strlen($content) >= $MIN_MESSAGE_LENGTH) {
                            $newMsg = new ForumMessages(['content' => $content, 'parent_message_id' => $respond_to, 'user_id' => Yii::$app->user->id]);
                            if ($newMsg->save()) {
                                return ['result' => 'ok',
                                    'new_msg_id' => $newMsg->id,
                                    'created_at' => $newMsg->created_at,
                                    'msg_content' => $newMsg->content]; //из расчета на применение цензурного контроля в будущем
                            } else return ['result' => 'error', 'code' => 3, 'message' => 'Не удалось сохранить сообщение.'];
                        } else return ['result' => 'error', 'code' => 6, 'message' => 'Отправленое сообщение пустое.'];
                    } else return ['result' => 'error', 'code' => 2, 'message' => 'Превышена допустимая длина сообщения.'];
                } else return ['result' => 'error', 'code' => 1, 'message' => 'Целевое сообщение для ответа не найдено.'];
            } else return ['result' => 'error', 'code' => 7, 'message' => 'Невозможно отправить сообщение: ваш аккаунт заблокирован или не существует.'];
        } else return $this->redirect(['forum/index']);
    }

    /**
     * Обновление сообщения пользователя
     * Происходит лишь при условии, что это сообщение пользователя и оно не слишком давно оставлено
     * @param integer $id
     * @param string $content
     * @return mixed
     */
    public function actionUpdateMessage($id, $content)
    {
        //todo сделать поправку на текущий часовой пояс при сравнении (в базу пишется время по Гринвичу)
        //todo максимальное количество прошедших часов для редактирования подтягивать из таблицы глобальных настроек, пока что это будет 5 часов
        //todo разрешить только через post
        $MAX_DAYS_SINCE_CREATED = 1; //редактировать можно не позднее, чем через день после создания
        $MAX_MESSAGE_LENGTH = 1500;
        $MIN_MESSAGE_LENGTH = 1;

        Yii::$app->response->format = Response::FORMAT_JSON;
        if(Yii::$app->request->isAjax) {
            if(Yii::$app->user->id) { //проверка того, что пользователь залогинен todo позже добавить проверку, не находится ли залогиненный пользователь в бан-листе
                $targetMessage = ForumMessages::findOne($id);
                if($targetMessage) { //проверка существования сообщения, которое пытаемся обновить
                    if($targetMessage->user_id == Yii::$app->user->id) { //проверка принадлежности рекактируемого сообщения автору
                        //todo корректировку по часовым поясам
                        $time_now = new DateTime();
                        $time_created_at = new DateTime($targetMessage->created_at);
                        $interval = $time_now->diff($time_created_at,true)->days;
                        if($interval < $MAX_DAYS_SINCE_CREATED) { //проверка срока давности с момента создания
                            if(strlen($content) <= $MAX_MESSAGE_LENGTH) { //проверка длины текста, слишком длинное сообщение отказываемся сохранять
                                if(strlen($content) >= $MIN_MESSAGE_LENGTH) { //проверка длины текста, слишком короткое сообщение отказываемся сохранять
                                    $targetMessage->content = $content;
                                    if ($targetMessage->save()) {
                                        return ['result' => 'ok', 'new_content' => $content]; //на будущее для возможности добавления цензурирования
                                    } else return ['result' => 'error', 'code' => 5, 'message' => 'Не удалось обновить сообщение.'];
                                } else return ['result' => 'error', 'code' => 6, 'message' => 'Обновляемое сообщение пустое.'];
                            } else return ['result' => 'error', 'code' => 4, 'message' => 'Превышена допустимая длина сообщения.'];
                        } else return ['result' => 'error', 'code' => 3, 'message' => 'Истек допустимый срок для редактируемого сообщения.'];
                    } else return ['result' => 'error', 'code' => 2, 'message' => 'Вы пытаетесь редактировать чужое сообщение.'];
                } else return ['result' => 'error', 'code' => 1, 'message' => 'Целевое сообщение для обновления не найдено.'];
            } else return ['result' => 'error', 'code' => 7, 'message' => 'Невозможно отправить сообщение: ваш аккаунт заблокирован или не существует.'];
        } else return $this->redirect(['forum/index']);
    }

    /**
     * Удаление существующего принадлежащего пользователю сообщения, с непросроченным сроком давности с момента создания.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDeleteMessage($id)
    {
        // todo добавить в правилах разрешение выполнять эту функцию только через метод post
        $MAX_DAYS_SINCE_CREATED = 1; //редактировать можно не позднее, чем через день после создания

        Yii::$app->response->format = Response::FORMAT_JSON;
        if(Yii::$app->request->isAjax) {
            $targetMessage = ForumMessages::findOne($id);
            if($targetMessage) { //проверка существования сообщения, которое пытаемся удалить
                if($targetMessage->user_id == Yii::$app->user->id) { //проверка принадлежности удаляемого сообщения автору
                    if(Yii::$app->user->id) { //проверка того, что пользователь залогинен todo позже добавить проверку, не находится ли залогиненный пользователь в бан-листе
                        //todo корректировку по часовым поясам
                        $time_now = new DateTime();
                        $time_created_at = new DateTime($targetMessage->created_at);
                        $interval = $time_now->diff($time_created_at,true)->days;
                        if($interval <= $MAX_DAYS_SINCE_CREATED) { //проверка срока давности с момента создания
                            if($targetMessage->delete()) {
                                return ['result' => 'ok'];
                            } else return ['result' => 'error', 'code' => 4, 'message' => 'Не удалось удалить сообщение.'];
                        } else return ['result' => 'error', 'code' => 3, 'message' => 'Истек допустимый срок для удаляемого сообщения.'];
                    } else return ['result' => 'error', 'code' => 2, 'message' => 'Вы пытаетесь редактировать чужое сообщение.'];
                } else return ['result' => 'error', 'code' => 7, 'message' => 'Невозможно отправить сообщение: ваш аккаунт заблокирован или не существует.'];
            } else return ['result' => 'error', 'code' => 1, 'message' => 'Целевое сообщение для обновления не найдено.'];
        } else return $this->redirect(['forum/index']);
    }

    /**
     * реализует голосование (лайк, убрать лайк, дизлайк, убрать дизлайк
     * @param $msg_id
     * @param string $action = {set,cancel}
     * @param integer $value = {-1 - dislike, 0 - nothing, 1 - like}
     * @return array|Response
     */
    public function actionUpdateVoting($msg_id, $action, $value = 0)
    { //todo эту функцию
        if(Yii::$app->request->isAjax) {
            if(abs($value) <= 1) {
                switch ($action) {
                    case "set" : {
                        //поиск уже поставленной оценки
                        //если поставлена такая же оценка - то выдать сообщение об ошибке
                        //если поставлена другая оценка - изменить её и вернуть соббщение об успехе
                        break;
                    }
                    case "cancel" : {
                        //если оценка найдена - удалить её и вернуть положительный результат
                        //если не найдена - сообщение об ошибке
                        break;
                    }
                }
            } else return ['result' => 'error', 'message' => 'Не играйся с API, шалун.)'];
        } else return $this->redirect(['index']);
    }
}
