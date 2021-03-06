<?php

namespace frontend\assets;

use common\assets\VueAsset;
use yii\web\AssetBundle;

/**
 * Yunhetong object methods
 */
class YhtAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
        'js/yht.js',
        'https://api.yunhetong.com/api_page/api/yht.js'
    ];
    public $depends = [
        VueAsset::class
    ];
}
