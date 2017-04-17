<?php

namespace frontend\controllers;

use common\models\TestTabulation;

class TestPlaygroundController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index', ['tabulation' => new TestTabulation()]);
    }

}
