<?php
use yii\helpers\ArrayHelper;

$config = [
    'id' => 'yii2-profiler-test-app',
    'basePath' => dirname(__DIR__),

    'components' => [

        'profilerFake' => [
            'class' => \cvsoft\profiler\Component::class,
            'scriptBeginTimeConst' => 'UNDEFINED_CONST',
        ],
        'profiler' => [
            'class' => \cvsoft\profiler\Component::class,
            'scriptBeginTimeConst' => 'DEFINED_CONST',
        ],
    ],

    'params' => require __DIR__ . '/params.php',
];

if (file_exists(__DIR__ . '/main-local.php')) {
    return ArrayHelper::merge($config, require __DIR__ . '/main-local.php');
} else {
    return $config;
}
