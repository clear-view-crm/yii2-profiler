<?php
namespace cvsoft\profiler;

use cvsoft\profiler\activities\ConsoleRequestActivity;
use cvsoft\profiler\activities\DefaultActivity;
use cvsoft\profiler\activities\WebRequestActivity;

/**
 * Компонент приложения для ведения таймингов запросов и их логов
 *
 * При инициализации компонента расчитывается микровремя начала выполнения скрипта.
 * По умолчанию началом выполнения будет считаться момент инициализации данного компонента. Для более точного замера
 * времени рекомендуется определить константу в начале входного скрипта
 * ```php
 *    define('SOME_CONSTANT_NAME', microtime(true));
 * ```
 * и указать имя заданной константы в конфигурации компонента:
 * ```php
 *    'components' => [
 *        'profiler' => [
 *            'class' => 'cvsoft\profiler\Component',
 *            'scriptBeginTimeConst' => 'SOME_CONSTANT_NAME'
 *        ],
 *        // другие компоненты приложения
 *    ],
 * ```
 *
 * @property-read float $beginTime
 * @property-read float $currentTime
 *
 * @package cvsoft\profiler
 * @author  Alexey Volkov <webwizardry@hotmail.com>
 */
class Component extends \yii\base\Component
{
    /**
     * @var string имя константы, из которой будет получено значение микровремени начала обработки скрипта
     *    если не задано имя константы или указанная константа не объявлена или содержит не float значение, то
     *    временем начала выполнения будет считаться момент инициализации компонента
     */
    public $scriptBeginTimeConst = null;

    /**
     * @var float время начала выполнения скрипта
     */
    private $_scriptBeginMicroTime = null;
    /**
     * @var array конфигурации записей действий по тегам
     */
    private $_activities = [
        'console' => ['class' => ConsoleRequestActivity::class],
        'web'     => ['class' => WebRequestActivity::class],
        'default' => ['class' => DefaultActivity::class]
    ];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->scriptBeginTimeConst && defined($this->scriptBeginTimeConst)) {
            $beginTimeValue = constant($this->scriptBeginTimeConst);
            if (is_float($beginTimeValue)) {
                $this->_scriptBeginMicroTime = $beginTimeValue;
            }
        }
        if (null == $this->_scriptBeginMicroTime) {
            $this->_scriptBeginMicroTime = $this->currentTime;
        }
    }

    public function getBeginTime()
    {
        return $this->_scriptBeginMicroTime;
    }

    public function getCurrentTime()
    {
        return microtime(true);
    }
}
