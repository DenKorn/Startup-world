<?php
/* @var $this yii\web\View */
/* @var $tabulation TestTabulation */
/* @var $testResults array */

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
            <h3>Значения элементов в индексах:</h3>
            <p>0: <?= var_export($tabulation->getElementByNumber(0)) ?></p>
            <p>140: <?= var_export($tabulation->getElementByNumber(140)) ?></p>
            <p>399: <?= var_export($tabulation->getElementByNumber(399)) ?></p>
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
            <h3>Результаты тестирования:</h3>
            <? foreach ($testResults as $result): ?>
                <p>
                    <?=$result['description']?>:
                    <? if($result['value'] == true): ?>
                        <span class="label label-success">Ок</span>
                    <? else: ?>
                        <span class="label label-danger">Ошибка</span>
                    <? endif; ?>
                </p>
            <? endforeach; ?>
        </div>
    </div>
</div>

<div class="row col-md-offset-1 col-md-10">
    <div class="panel panel-default center-block">
        <div class="panel-body">
            <h3>Значения в виде графика:</h3>
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

<div class="row col-md-offset-1 col-md-10">
    <div class="panel panel-default center-block">
        <div class="panel-body">
            <h3>Значения всех элементов:</h3>
            <div class="col-lg-6 col-lg-offset-3">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>X</th>
                            <th>Y</th>
                        </tr>
                    </thead>
                    <tbody>
                    <? for ($i = 0; $i < $len; $i++): ?>
                        <tr <?=($i == 0 || $i == 140 || $i == 399) ? 'class="success"' : '  '?>>
                            <td><?=$i?></td>
                            <td><?=$valX[$i]?></td>
                            <td><?=$valY[$i]?></td>
                        </tr>
                    <? endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>