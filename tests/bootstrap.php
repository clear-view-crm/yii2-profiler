<?php
/**
 * Входной скрипт тестового приложения.
 * Создает фронт-контроллер приложения, но НЕ ЗАПУСКАЕТ выполнение приложения,
 */

define('DEFINED_CONST', microtime(true));

require_once dirname(__DIR__ ) . '/vendor/autoload.php';
require_once dirname(__DIR__ ) . '/vendor/yiisoft/yii2/Yii.php';
$config = require dirname(__DIR__) . '/src-test/config/main.php';
$app = new \yii\web\Application($config);
