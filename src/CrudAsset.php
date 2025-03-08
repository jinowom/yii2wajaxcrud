<?php 

namespace jinowom\wajaxcrud;

use yii\web\AssetBundle;

/**
 * @author John Martin <john.itvn@gmail.com>
 * @since 1.0
 */
class CrudAsset extends AssetBundle
{
    public $sourcePath = '@vendor/jinowom/yii2wajaxcrud/src/assets';
    /* 
     * 方法一：public $sourcePath = '@wajaxcrud/assets';
     * 这个使用'@wajaxcrud/assets别名；@wajaxcrud别名定义 可写在 /common/config/bootstrap.php 里
     * Yii::setAlias('@wajaxcrud', dirname(dirname(__DIR__)) . '/vendor/jinowom/yii2wajaxcrud/src/');//jinowom/yii2wajaxcrud组件加载的别名
     * 方法二：public $sourcePath = '@vendor/jinowom/yii2wajaxcrud/src/assets';
     * //public $sourcePath = '@vendor/jinowom/yii2wajaxcrud/src/assets';// 这个写法，直接使用@vendor也可以
    */
    
//    public $publishOptions = [
//        'forceCopy' => true,
//    ];

    public $css = [
        'ajaxcrud.css',
        'baguetteBox.min.css',
    ];

    public $js = [
        YII_ENV_DEV?'ModalRemote.js':'ModalRemote.min.js',
        YII_ENV_DEV?'ajaxcrud.js':'ajaxcrud.min.js',
        'baguetteBox.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'kartik\grid\GridViewAsset',
    ];
}
