<?php
$rootDir = dirname(dirname(__DIR__));
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@frontend', $rootDir . '/frontend');
Yii::setAlias('@backend', $rootDir . '/backend');
Yii::setAlias('@console', $rootDir . '/console');