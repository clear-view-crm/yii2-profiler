<?php
namespace cvsoft\profiler;

use cvsoft\profiler\activities\ConsoleRequestActivity;
use cvsoft\profiler\activities\DefaultActivity;
use cvsoft\profiler\activities\WebRequestActivity;
use yii\web\Application as ApplicationWeb;
use yii\console\Application as ApplicationConsole;

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
 * @property-read Activity $current
 * @property-read Activity $root
 * @property-read bool $isRoot
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
     * @var null|Activity корневая запись лога (консольная команда или HTTP запрос)
     */
    private $_rootActivity = null;
    private $_currentActivity = null;

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

    /**
     * Возвращает ссылку на корневую запись лога
     * @return Activity|null
     */
    public function getRoot()
    {
        return $this->_rootActivity;
    }

    /**
     * Возвращает ссылку на текущую запись лога
     * @return Activity|null
     */
    public function getCurrent()
    {
        return $this->_currentActivity;
    }

    /**
     * Возвращает true, если указатель текущей записи указывает на корневую запись лога
     * @return bool
     */
    public function getIsRoot()
    {
        return ($this->_currentActivity === $this->_rootActivity);
    }

    /**
     * Формирует корневую запись лога
     * @throws \yii\base\InvalidConfigException
     */
    public function enable()
    {
        $rootTag = null;
        if (\Yii::$app instanceof ApplicationWeb) {
            $rootTag = 'web';
        }
        if (\Yii::$app instanceof ApplicationConsole) {
            $rootTag = 'console';
        }
        if ($rootTag) {
            $this->_rootActivity = $this->_createActivity($rootTag);
            $this->_currentActivity = &$this->_rootActivity;
        }
    }

    /**
     * Создает новую дочернюю запись в текущей
     * @param string $comment
     * @param string $tag
     * @return Activity|null
     * @throws \yii\base\InvalidConfigException
     */
    public function append($comment = '', $tag = 'default')
    {
        $appended = $this->current->appendChild($this->_createActivity($tag, ['comment' => $comment]));
        $this->_currentActivity = &$appended;
        return $this->_currentActivity;
    }

    public function reset()
    {
        if (!$this->isRoot) {

        }
    }

    public function commit($message = null)
    {
        $parent = $this->_currentActivity->commit($message);
        $this->_currentActivity = &$parent;
    }

    public function rollback($message = null)
    {
        $parent = $this->_currentActivity->rollback($message);
        $this->_currentActivity = &$parent;
    }

    public function destroy()
    {

    }

    public function getBeginTime()
    {
        return $this->_scriptBeginMicroTime;
    }

    public function getCurrentTime()
    {
        return microtime(true);
    }

    public function save()
    {
        //@todo сохранить дерево
        $this->destroy();
    }

    /**
     * @param string $tag
     * @param array $config
     * @return object|null
     * @throws \yii\base\InvalidConfigException
     */
    private function _createActivity($tag = 'default', $config = [])
    {
        if (!array_key_exists($tag, $this->_activities)) $tag = 'default';
        if (isset($this->_activities[$tag])) {
            $config = array_merge($this->_activities[$tag], $config);
            return \Yii::createObject($config);
        }
        return null;
    }
}
