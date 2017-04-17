<?php

namespace common\models;

class TestTabulation
{
    private $parameter = 2.4;
    private $bounds = [1, 5];
    private $step = 0.01;
    private $valuesX = null;
    private $valuesY = null;
    private $stepsCount = null;
    private $minYValueIndexCached = null;
    private $maxYValueIndexCached = null;
    private $sumCached = null;
    private $averageValueCached = null;

    /**
     * Вычисляет значение y, основываясь на значении x и дополнительном параметре
     *
     * @param float $x
     * @return float
     */
    public function getValueAt ($x)
    {
        $a = $this->parameter;

        if ( $x > $this->parameter )
        {
            return $x * sqrt( $x - $a );
        }
        elseif ( $x < $a )
        {
            return $x * sin( $x * $a );
        }
        else
        {
            return exp( - $x * $a) * cos( $x * $a );
        }
    }

    public function getStep ()
    {
        return $this->step;
    }

    public function getLeftBound ()
    {
        return $this->bounds[0];
    }

    public function getRightBound ()
    {
        return $this->bounds[1];
    }

    /**
     * @return float количество шагов для табуляции
     */
    public function getStepsCount ()
    {
        if (!$this->stepsCount)
        {
            $this->stepsCount = round( ( $this->bounds[1] - $this->bounds[0] ) / $this->step );
        }

        return $this->stepsCount;
    }

    private function tabulate ()
    {
        $this->valuesX = [];
        $this->valuesY = [];
        $tempX = $this->bounds[0];

        for ( $i = 0; $i < $this->getStepsCount(); $i++ )
        {
            $this->valuesX[] = $tempX;
            $this->valuesY[] = $this->getValueAt($tempX);
            $tempX += $this->step;
        }
    }

    /**
     * отдает значение функции по номеру
     *
     * @param $number
     * @return array|bool
     */
    public function getElementByNumber ( $number )
    {
        if ($number < 0 || $number > $this->getStepsCount() - 1) return false;

        if ( !$this->valuesX || !$this->valuesY )
        {
            $this->tabulate();
        }

        return [
            'x' => $this->valuesX[$number],
            'y' => $this->valuesY[$number]
        ];
    }

    public function getValuesX ()
    {
        if ( ! $this->valuesX )
        {
            $this->tabulate();
        }

        return $this->valuesX;
    }

    public function getValuesY ()
    {
        if ( ! $this->valuesY )
        {
            $this->tabulate();
        }

        return $this->valuesY;
    }

    public function getMaxElementIndex ()
    {
        $valuesY = $this->getValuesY();

        if ( ! $this->minYValueIndexCached )
        {
            $this->minYValueIndexCached = array_keys($valuesY, max($valuesY))[0];
        }

        return $this->minYValueIndexCached;
    }

    public function getMinElementIndex ()
    {
        $valuesY = $this->getValuesY();

        if ( ! $this->minYValueIndexCached )
        {
            $this->minYValueIndexCached = array_keys($valuesY, min($valuesY))[0];
        }

        return $this->minYValueIndexCached;
    }

    public function getSum ()
    {
        if ( ! $this->sumCached )
        {
            $values = $this->getValuesY();
            $sum = 0;

            foreach ($values as $value) {
                $sum += $value;
            }

            $this->sumCached = $sum;
        }

        return $this->sumCached;
    }

    public function getAverageValue ()
    {
        if ( ! $this->averageValueCached )
        {
            $this->averageValueCached = $this->getSum() / $this->getStepsCount();
        }

        return $this->averageValueCached;
    }

    public function reset ()
    {
        $this->valuesX = null;
        $this->valuesY = null;
        $this->stepsCount = null;
        $this->minYValueIndexCached = null;
        $this->maxYValueIndexCached = null;
        $this->sumCached = null;
        $this->averageValueCached = null;
    }
}
