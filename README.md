yii2wajaxcrud 
=============

[![Latest Stable Version](https://poser.pugx.org/wodrow/yii2wajaxcrud/v/stable)](https://packagist.org/packages/wodrow/yii2wajaxcrud)
[![License](https://poser.pugx.org/wodrow/yii2wajaxcrud/license)](https://packagist.org/packages/wodrow/yii2wajaxcrud)
[![Total Downloads](https://poser.pugx.org/wodrow/yii2wajaxcrud/downloads)](https://packagist.org/packages/wodrow/yii2wajaxcrud)

Gii CRUD template for Single Page Ajax Administration for yii2 

+ Requires yiisoft/yii2-bootstrap5:"^2.0.4"
+ Requires yiisoft/yii2-bootstrap:"^2.0.0"


Features
------------
+ Create, read, update, delete in onpage with Ajax
+ Bulk delete suport
+ Pjax widget suport
+ Export function(pdf,html,text,csv,excel,json)
+ Editable suport
+ Daterange suport

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist jinowom/yii2wajaxcrud "^1.0.0"
or
php composer.phar require --prefer-dist jinowom/yii2wajaxcrud "^1.0.2"
```

or add

```
"jinowom/yii2wajaxcrud": "^1.0.0"
or
"jinowom/yii2wajaxcrud": "^1.0.2"
```

to the require section of your `composer.json` file.


Usage
-----
For first you must enable Gii module Read more about [Gii code generation tool](http://www.yiiframework.com/doc-2.0/guide-tool-gii.html)

Because this extension used [kartik-v/yii2-grid](https://github.com/kartik-v/yii2-grid) extensions so we must config gridview module before

Let 's add into modules config in your main config file
````php
'modules' => [
    'gridview' =>  [
        'class' => '\kartik\grid\Module'
    ]       
]
````

gii config like
````php
$config['bootstrap'][] = 'gii';
$config['modules']['gii'] = [
    'class' => 'yii\gii\Module',
];
$config['modules']['gii']['generators']['wodrowmodel'] = [
    'class' => \jinowom\wajaxcrud\generators\model\Generator::class,
    'showName' => "YOUR MODEL GENERATOR",
];
$config['modules']['gii']['generators']['wodrowwajaxcrud'] = [
    'class' => \jinowom\wajaxcrud\generators\crud\Generator::class,
    'showName' => "YOUR AJAX CRUD GENERATOR",
];
````

You can then access Gii through the following URL:

http://localhost/path/to/index.php?r=gii

and you can see <b>YOUR AJAX CRUD GENERATOR</b>

![exp1](https://i.loli.net/2019/05/09/5cd3a7c2cb95a.png)
![exp2](https://i.loli.net/2019/05/09/5cd3a7c2cee7a.png)
![exp3](https://i.loli.net/2019/05/09/5cd3a7c2d14a9.png)

Others
------------
```text
#只对github.com
git config --global http.https://github.com.proxy socks5://127.0.0.1:1080
```
感谢
------------
```text
#wodrow <wodrow451611cv@gmail.com>
#Kartik Visweswaran <kartikv2@gmail.com>
```