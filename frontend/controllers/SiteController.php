<?php
namespace frontend\controllers;

use common\models\LoginForm;
use common\models\SignUpForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;

class SiteController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error','index','sign-up'],
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
        /*
        $user = new User();
        $user->username = 'admin';
        $user->setPassword('admin');
        $user->generateAuthKey();
        $user->save();
        */


    }

}
