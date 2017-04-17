<?php
/* @var $this yii\web\View */
/* @var $tabulation TestTabulation */

use yii\helpers\Html;
use common\models\TestTabulation;

$ROUND_PRECISION = 3;

$this->title = 'Табулирование функции';
?>

<div class="row col-md-offset-1 col-md-10">
    <div class="panel panel-default">
        <div class="panel-body text-center">
            <h1><?= Html::encode($this->title) ?></h1>
            <p>Проект для лабораторной работы №2 по предмету "WEB-технологии"</p>
        </div>
    </div>
</div>

<div class="row col-md-offset-1 col-md-10">
    <div class="panel panel-default center-block">
        <div class="panel-body">
            <p>Значения элементов в индексах:</p>
            <p>0: <?= var_export($tabulation->getElementByNumber(0)) ?></p>
            <p>140: <?= var_export($tabulation->getElementByNumber(140)) ?></p>
            <p>400: <?= var_export($tabulation->getElementByNumber(400)) ?></p>
            <br>
            <p>Количетво шагов для табулирования: <?= $tabulation->getStepsCount() ?></p>
            <p>Шаг: <?= $tabulation->getStep() ?></p>
            <p>Границы: [<?=$tabulation->getLeftBound()?>, <?=$tabulation->getRightBound()?>]</p>
            <p>Номер наибольшего значения функции: <?=$tabulation->getMaxElementIndex()?>
            (x: <?=round($tabulation->getValuesX()[$tabulation->getMaxElementIndex()], $ROUND_PRECISION)?>,
                <?=round($tabulation->getValuesY()[$tabulation->getMaxElementIndex()], $ROUND_PRECISION)?>)
            </p>
            <p>Номер наименьшего значения функции: <?=$tabulation->getMinElementIndex()?>
                (x: <?=round($tabulation->getValuesX()[$tabulation->getMinElementIndex()], $ROUND_PRECISION)?>,
                <?=round($tabulation->getValuesY()[$tabulation->getMinElementIndex()], $ROUND_PRECISION)?>)
            </p>
            <p>Сумма значений элементов: <?=round($tabulation->getSum(), $ROUND_PRECISION)?></p>
            <p>Среднее арифметическое: <?=round($tabulation->getAverageValue(), $ROUND_PRECISION)?></p>
        </div>
    </div>
</div>

<div class="row col-md-offset-1 col-md-10">
    <div class="panel panel-default center-block">
        <div class="panel-body">
            <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
            <div id="chart_div"></div>
            <script>
                google.charts.load('current', {packages: ['corechart', 'line']});
                google.charts.setOnLoadCallback(drawBackgroundColor);

                function drawBackgroundColor() {
                    let data = new google.visualization.DataTable();
                    data.addColumn('number', 'X');
                    data.addColumn('number', 'Y');
                    <?
                        $valX = $tabulation->getValuesX();
                        $valY = $tabulation->getValuesY();
                        $len = count($valX);
                    ?>
                    data.addRows([
                        <? for ($i = 0; $i < $len; $i++)
                            {
                                echo "[".$valX[$i].",".$valY[$i]."]";
                                if ($i != $len) echo ", ";
                            }
                        ?>
                    ]);

                    let options = {
                        backgroundColor: '#fff'
                    };

                    let chart = new google.visualization.LineChart(document.getElementById('chart_div'));
                    chart.draw(data, options);
                }
            </script>
        </div>
    </div>
</div>
