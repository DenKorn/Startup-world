<?php

use yii\helpers\Html;

$this->title = 'Статистика форума';
$this->registerJsFile('https://www.gstatic.com/charts/loader.js');
$this->registerJsFile('http://underscorejs.ru/underscore.js');
$this->registerJsFile('@web/js/stats-controller.js');
?>

<div class="row">
    <div class="col-md-offset-1 col-md-10 col-lg-offset-0 col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading text-center">
                <strong><?= Html::decode($this->title) ?></strong>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-offset-1 col-md-10 col-lg-offset-0 col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading text-center">
                <strong>Активность на форуме</strong>
            </div>
            <div class="panel-body" id="chart_div" style="padding: 0">

            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-offset-1 col-md-10 col-lg-offset-0 col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading text-center">
                <strong>Самые популярные пользователи</strong>
            </div>
            <div class="panel-body">
                Бан этих пользователей нанёс бы серьезный ущерб форуму.
                <table class="table table-striped table-hover text-center">
                    <tbody id="most_rated_users_table_container">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-offset-1 col-md-10 col-lg-offset-0 col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading text-center">
                <strong>Самые непопулярные пользователи</strong>
            </div>
            <div class="panel-body">
                На этих пользователей стоит обратить внимание, возможно некоторых из них стоит заблокировать.
                <table class="table table-striped table-hover text-center">
                    <tbody id="less_rated_users_table_container">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
