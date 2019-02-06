<?php
namespace cvsoft\profiler\activities;

class WebRequestActivity extends AbstractActivity
{
    public $tag = 'http';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->tag = \Yii::$app->request->isSecureConnection ? 'https' : 'http';
    }
}
