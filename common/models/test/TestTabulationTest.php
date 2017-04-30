<?php

namespace common\models\test;

use common\models\TestTabulation;

class TestTabulationTest {

    private $parameter;
    private $leftBound;
    private $rightBound;
    private $eps = 1;

    private function getClassExemplar()
    {
        return new TestTabulation($this->parameter, $this->leftBound, $this->rightBound);
    }

    public function __construct($parameter, $leftBound, $rightBound)
    {
        $this->setParameters($parameter, $leftBound, $rightBound);
    }

    public function setParameters($parameter, $leftBound, $rightBound)
    {
        $this->parameter = $parameter;
        $this->leftBound = $leftBound;
        $this->rightBound = $rightBound;
    }

    /**
     * Тест вычисления значения функции
     */
    public function testValueCalc()
    {
        $testRes = true;
        $expected = [[1, 0.67546318055115], [2.89, 	2.023], [5, 8.04]];

        $testSubject = $this->getClassExemplar();

        foreach ($expected as $test)
        {
            $testRes &= abs($testSubject->getValueAt($test[0]) - $test[1]) <= $this->eps;
        }

        return $testRes;
    }

    /**
     * Проверяет корректность вычисления основным классом количества шагов для табулирования функции
     * @return bool
     */
    public function testStepsCount()
    {
        $testRes = true;
        $expected = [[1, 5, 0.01, 400], [0, 1 ,0.1 ,10]];

        $testSubject = $this->getClassExemplar();

        foreach ($expected as $test)
        {
            $testSubject->setConfig($test[2],$test[0], $test[1]);
            $testRes &= $testSubject->getStepsCount() == $test[3];
        }

        return $testRes;
    }

    /**
     * Проверяет получение минимального и максимального значения среди всего того, что функция в классе вычисляет на заданном интервале
     */
    public function testMinMax()
    {
        $testRes = true;
        $params = [1, 5, 0.01];

        $testSubject = $this->getClassExemplar();
        $testSubject->setConfig($params[2],$params[0],$params[1]);

        $testRes &= abs($testSubject->getElementByNumber($testSubject->getMaxElementIndex())['y'] - 8.031) <= $this->eps ; // для максимального
        $testRes &= abs($testSubject->getElementByNumber($testSubject->getMinElementIndex())['y'] - -2.006) <= $this->eps ; // для минимального

        return $testRes;
    }

    public function testAverageValue()
    {
        $testRes = true;
        $params = [1, 5, 0.01, 2.412];

        $testSubject = $this->getClassExemplar();
        $testSubject->setConfig($params[2],$params[0],$params[1]);

        $testRes &= abs($testSubject->getAverageValue() - $params[3]) <= $this->eps;

        return $testRes;
    }

    public function testSumValue()
    {
        $testRes = true;
        $params = [1, 5, 0.01, 964.702];

        $testSubject = $this->getClassExemplar();
        $testSubject->setConfig($params[2],$params[0],$params[1]);

        $testRes &= abs($testSubject->getSum() - $params[3]) <= $this->eps;

        return $testRes;
    }

}