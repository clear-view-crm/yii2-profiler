<?php
namespace cvsoft\profiler;
use cvsoft\profiler\profiling\CliProfiler;
use cvsoft\profiler\profiling\HttpProfiler;
use yii\base\InvalidCallException;

class Profiler
{
    public static $classMap = [];

    private static $_profiler = null;

    /**
     * Включает профилирование действий пользователя
     * @param bool $throwException
     */
    public static function enable($throwException = true)
    {
        if (null === self::$_profiler) {
            $profilerClass = null;
            if (\Yii::$app instanceof \yii\web\Application)
                $profilerClass = self::$classMap['web'] ?? HttpProfiler::class;
            if (\Yii::$app instanceof \yii\console\Application)
                $profilerClass = self::$classMap['cli'] ?? CliProfiler::class;
            if (class_exists($profilerClass)) self::$_profiler = $profilerClass::open();
        } elseif ($throwException) {
            throw new InvalidCallException('Profiler is already enabled');
        }
    }

    /**
     * Уничтожает запись профайла запроса без записи лога
     */
    public static function destroy()
    {
        self::$_profiler = null;
    }

    /**
     * Записывает лог профайла
     */
    public static function commit()
    {
        if (self::$_profiler) {

            self::destroy();
        }
    }
}
