<?php
namespace cvsoft\profiler\profiling;
use yii\base\Component;

abstract class AbstractProfiler extends Component
{
    private $_scriptBeginTime = null;

    public static function loadFromSession()
    {
        return null;
    }

    public static function open()
    {
        if ($profiler = static::loadFromSession()) {
            return $profiler;
        }
        return new static();
    }

    public function init()
    {
        defined('CV_SCRIPT_BEGIN_TIME') or define('CV_SCRIPT_BEGIN_TIME', microtime(true));
        $this->_scriptBeginTime = CV_SCRIPT_BEGIN_TIME;
    }
}
