<?php

namespace jinowom\wajaxcrud;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;

/**
 * @author John Martin <john.itvn@gmail.com>
 * @since 1.0
 */
class Bootstrap implements BootstrapInterface {

    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     */
    public function bootstrap($app) {
        Yii::setAlias("@wajaxcrud", __DIR__);//这个Botstrap无法被调用，囧！！
        Yii::setAlias("@jinowom/wajaxcrud", __DIR__);//这个Botstrap无法被调用，囧！！
        /*
         * 既然不能被调用，就把如下两个放到/common/config/bootstrap.php 里。
         //Yii::setAlias('@wajaxcrud', dirname(dirname(__DIR__))  . '/vendor/jinowom/yii2wajaxcrud/src');
         //Yii::setAlias("@jinowom/wajaxcrud", dirname(dirname(__DIR__))  . '/vendor/jinowom/yii2wajaxcrud/src');
         * 
         */
        /*if ($app->hasModule('gii')) {
            if (!isset($app->getModule('gii')->generators['ajaxcrud'])) {
                $app->getModule('gii')->generators['ajaxcrud'] = 'jinowom\wajaxcrud\generators\Generator';
            }
        }*/
    }

}
