<?php

namespace frontend\controllers;

use common\models\ForumMessages;
use Yii;
use common\models\ForumRoots;
use common\models\ForumRootsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ForumController implements the CRUD actions for ForumRoots model.
 */
class ForumController extends Controller
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
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all ForumRoots models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ForumRootsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15; //todo установка количетсва тем на одной странице
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ForumRoots model.
     * @param integer $id
     * @return mixed
     */
    public function actionDiscussions($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreateTheme()
    {
        $themeModel = new ForumRoots();
        $msgModel = new ForumMessages();
        if($themeModel->load(Yii::$app->request->post()) && $msgModel->load(Yii::$app->request->post()) && $themeModel->save()){
            $msgModel->user_id = Yii::$app->user->id;
            $msgModel->root_theme_id = $themeModel->id;
            $msgModel->save();
            return $this->redirect([]); //todo редирект на страницу темы
        } else
            return 0; //форма добавления новой темы
        
    }

    public function actionLoad($content) {
        var_dump(Yii::$app->request->get());
        $model = new ForumMessages();
        $model->content = $content;
        $model->user_id = Yii::$app->user->id;
        $model->parent_message_id = null; //уст
        $model->save();
        var_dump($model);
    }

    /**
     * Updates an existing ForumRoots model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ForumRoots model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the ForumRoots model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ForumRoots the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ForumRoots::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
