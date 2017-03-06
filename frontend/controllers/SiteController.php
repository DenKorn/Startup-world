<?php
namespace frontend\controllers;

use common\models\LoginForm;
use common\models\SignUpForm;
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

        //Yii::$app->user->last_activity;

        //todo создать таблицу списка уведомлений
        //todo создать для неё модель
        //todo Добавить в модель методы для получения ошибок и простых уведомлений
        //todo если есть чё вывести - выводим с результатом "ок", иначе выдаем пустой ответ с результатом 'null'

        return ['result' => 'ok', 'alerts' => ['Тебя лайкнули, аутист <a href="http://photo.qip.ru/photo/wp2/115887636/xlarge/141981340.jpg">Узнать, кто</a>']];

        return ['result' => null];
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
