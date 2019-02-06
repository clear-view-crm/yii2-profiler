<?php
use yii\helpers\ArrayHelper;

$params = [

];

if (file_exists(__DIR__ . '/params-local.php')) {
    return ArrayHelper::merge($params, require __DIR__ . '/params-local.php');
} else {
    return $params;
}
