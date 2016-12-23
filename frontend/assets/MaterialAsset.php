<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class MaterialAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/material-icon.css',
        'css/bootstrap-material-design.min.css',
        'css/ripples.min.css'
    ];
    public $js = [
        'js/ripples.js',
        'js/material.js',
        'js/material-init.js'
    ];
    public $depends = [
        'frontend\assets\AppAsset'
    ];
}