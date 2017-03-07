<?php
namespace frontend\controllers;

use common\models\ForumNotifications;
use common\models\GeneralSettings;
use common\models\LoginForm;
use common\models\SignUpForm;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error','index','sign-up','check-notifications'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Формируем в ответ пользователю обьект с массивами alerts и warnings. Он должен содержать в себе сообщения либо обычные, либо с предупреждениями
     * Уведомления берутся лишь достаточно свежие, перед завершением функции отправляемые уведомления удаляются из списка уведомлений в БД
     *
     * @return array
     */
    public function actionCheckNotifications()
    {
        //todo добавить инициирование отправки уведомлений на почту трем случайным пользователям (мегакостыль), которые давно не заходили на форум
        Yii::$app->response->format = Response::FORMAT_JSON;

        if(Yii::$app->user->isGuest) return ['result' => 'error', 'message' => 'Не трогай API, ты не должен был видеть это сообщение. Скрипт не мог отправить этот запрос.'];

        $current_user = User::findOne(Yii::$app->user->id);

        //обновляем время последнего захода пользователя на сайт
        $current_user->actualizeOnlineStatus();

        $NOTIFICATIONS_SETTINGS = GeneralSettings::getSettingsObjByName('USER_NOTIFICATIONS');

        $alerts = ForumNotifications::getNotificationsForUser(Yii::$app->user->id, $NOTIFICATIONS_SETTINGS->min_notify_age_for_mailing_in_hours, 'alert', true);
        $warnings = ForumNotifications::getNotificationsForUser(Yii::$app->user->id, $NOTIFICATIONS_SETTINGS->min_notify_age_for_mailing_in_hours, 'warning', true);

        return [
            'result' => 'ok',
            'alerts' => $alerts,
            'warnings' => $warnings
            ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionSignUp()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $userToSignUpForm = new SignUpForm();

        //При отправке данных пытаемся регистрировать пользователя
        if ($userToSignUpForm->load(Yii::$app->request->post()) && $userToSignUpForm->signUp()) {
            return $this->goBack();
        } else {
            // Иначе выдаем ему пустую форму регистрации
            return $this->render('sign_up',[
                'model' => $userToSignUpForm
            ]);
        }
    }

}
