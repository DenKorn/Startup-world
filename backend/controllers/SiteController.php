<?php
namespace backend\controllers;


use DateTime;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [

                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['admin'],
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

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Возвращает статистику о форуме в виде массива массивов, для google charts
     * использует 3 таблицы: user, forum_messages, forum_votes
     */
    public function actionGetForumStats()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $MONTH_MAP = ['январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'];

        function getQueryString ($table, $mainField, $countColumnName) {
          return "
            SELECT MONTH($mainField) as month,
                   count(*) as $countColumnName
            FROM $table
            WHERE $mainField >= DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR )
            GROUP BY month
            ORDER BY $mainField DESC; 
            ";
        };

        Yii::$app->db->createCommand('SET  SQL_MODE = ""')->execute();

        $registrations = Yii::$app->db->createCommand(getQueryString('user','registered_at','registers'))->queryAll();
        $messaging = Yii::$app->db->createCommand(getQueryString('forum_messages','created_at','creations'))->queryAll();
        $voting = Yii::$app->db->createCommand(getQueryString('forum_votes','setting_date','votes'))->queryAll();

        $currentMonth = (int)(new DateTime())->format('m');
        $data = [['X','Регистрации','Сообщения','Голосование']];

        for($i = 1 ; $i <= 12; $i++) {
            $monthNum = (($i - 1 + $currentMonth) % 12) + 1;

            $searchCallback = function ($item) use ($monthNum) {
                return $item["month"] == $monthNum;
            };

            $monthRegistrations = array_filter($registrations, $searchCallback);
            $monthRegistrations = $monthRegistrations ? +current($monthRegistrations)['registers'] : 0;

            $monthMessaging = array_filter($messaging, $searchCallback);
            $monthMessaging = $monthMessaging ? +current($monthMessaging)['creations'] : 0;

            $monthVoting = array_filter($voting, $searchCallback);
            $monthVoting = $monthVoting ? +current($monthVoting)['votes'] : 0;

            $data[] = [$MONTH_MAP[$monthNum-1], $monthRegistrations, $monthMessaging, $monthVoting];
        }

        return $data;
    }

    /**
     * выдает топ 10 пользователей с самым высоким, и с самым низким рейтингом в виде двух массивов
     * @param $best_users boolean true - best users, false - worst users
     * @param $users_count int количество пользователей, топ которых будет отображаться
     */
    public function actionGetOutstandingUsers($best_users, $users_count = 10)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $order = $best_users == "true" ? "DESC" : "ASC";
        $comparator = $best_users == "true" ? ">" : "<";

        return Yii::$app->db->createCommand("
        SELECT *
        FROM (
              SELECT
              user_id,
              SUM(last_calculated_rating) as sum_rating
              FROM forum_messages
              GROUP BY user_id
              ) as sum_msg_rating
        WHERE sum_rating $comparator 0
        ORDER BY sum_rating $order
        LIMIT $users_count;
        ")->queryAll();
    }

    /**
     * Проверка на предмет снятия с админа полномочий, для как можно более быстрого его выхода из системы
     *
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        if(! Yii::$app->user->isGuest && !Yii::$app->user->can('admin')) {
            Yii::$app->user->logout();
            return true;
        } else {
            return true;
        }
    }

    /**
     * По умолчанию мы отображаем залогиненому админу статистику, остальных перенаправляем на страницу входа
     *
     * @return string
     */
    public function actionIndex()
    {
        // если вошел не админ - сразу посылаем его
        if(! Yii::$app->user->can('admin')) {
            return $this->redirect(['site/login']);
        }

        //отображение для админа страницы со статистикой
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->loginAdminPanel()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
}
