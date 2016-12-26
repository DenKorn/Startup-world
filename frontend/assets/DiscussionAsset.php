<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class DiscussionAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/discussions-styles.css',
    ];
    public $js = [
        'js/messaging-controller.js'
    ];
    public $depends = [

    ];
}