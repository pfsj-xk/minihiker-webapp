<?php
namespace backend\assets;

use yii\bootstrap\BootstrapAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

class QaAsset extends AssetBundle
{
    public $basePath = '@staticPath';
    public $baseUrl = '@staticUrl';
    public $js = [
        YII_DEBUG ? 'css/qa.js' : 'css/qa.min.js'
    ];
    public $depends = [
        BackendAsset::class,
        YiiAsset::class,
        BootstrapAsset::class,
    ];
}
