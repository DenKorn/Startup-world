<?php

namespace frontend\controllers;

use common\models\test\TestTabulationTest;
use common\models\TestTabulation;

class TestPlaygroundController extends \yii\web\Controller
{
    public function actionIndex($parameter = 2.4, $leftBound = 1, $rightBound = 5)
    {
        $testClass = new TestTabulationTest($parameter, $leftBound, $rightBound);
        $testResults = [];

        $testResults[] = ['value' => $testClass->testValueCalc(), 'description' => 'Извлечение значения функции'];
        $testResults[] = ['value' => $testClass->testStepsCount(), 'description' => 'Извлечение количества шагов для заданного интервала'];
        $testResults[] = ['value' => $testClass->testMinMax(), 'description' => 'Извлечение максимального и минимального значения'];
        $testResults[] = ['value' => $testClass->testAverageValue(), 'description' => 'Извлечение среднего арифметического на заданном интервале'];
        $testResults[] = ['value' => $testClass->testSumValue(), 'description' => 'Извлечение суммы значений массива результатов табулируемой функции'];

        return $this->render('index', [
            'tabulation' => new TestTabulation($parameter, $leftBound, $rightBound),
            'testResults' => $testResults
        ]);
    }

}
