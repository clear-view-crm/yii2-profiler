<?php
namespace cvsoft\profiler\activities;
use cvsoft\profiler\Activity;
use yii\base\BaseObject;
use yii\base\InvalidCallException;

/**
 * Реализация базовых методов записи лога
 * @package cvsoft\profiler\activities
 * @author Alexey Volkov <webwizardry@hotmail.com>
 */
abstract class AbstractActivity extends BaseObject implements Activity
{
    public $tag = 'default';
    public $comment = null;
    public $message = null;

    /**
     * @var null|Activity родительская запись лога
     */
    protected $_parent   = null;
    /**
     * @var Activity[] вложенные (дочерние) записи лога
     */
    protected $_children = [];
    /**
     * @var array дополнительные данные, которые может получать запись
     */
    protected $_additionalData = [];
    /**
     * @var null время начала выполнения действия
     */
    protected $_activityBeginTime = null;
    /**
     * @var null время завершения выполнения действия
     */
    protected $_activityEndTime   = null;
    /**
     * @var bool флаг выполнения инициализации
     *     устанавливается в true методом [[init()]], при установленном в true значении становятся недоступными
     *     для установки некоторые служебные параметры записи
     */
    protected $_initDone = false;
    /**
     * Количество вложенных записей в следующем уровне
     * @var int
     */
    protected $_childrenCount = 0;

    protected $_commitStatus = self::STATUS_NONE;

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->_additionalData)) {
            $this->_additionalData[$name] = $value;
        } elseif (!$this->commitDone) {
            parent::__set($name, $value);
        }
    }

    public function getCommitDone()
    {
        return ($this->_commitStatus !== self::STATUS_NONE);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_additionalData)) {
            return $this->_additionalData[$name];
        }
        return parent::__get($name);
    }

    /**
     * Устанавливает родительскую по отношению к данной запись
     * @param Activity $activity
     */
    public function setParent(Activity $activity)
    {
        if (null == $this->_parent) {
            $this->_parent = $activity;
        }
    }

    /**
     * Позволяет установить время начала выполнения действия при помощи параметра конфигурации
     * @param float $time
     * @throws InvalidCallException если установка времени вызывается после инициализации
     */
    public function setBeginTime(float $time)
    {
        if (!$this->_initDone) {
            $this->_activityBeginTime = $time;
        } else {
            throw new InvalidCallException('Start time can be defined before init() only');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if (null === $this->_activityBeginTime) {
            $this->_activityBeginTime = microtime(true);
        }
        $this->_initDone = true;
    }

    /**
     * {@inheritdoc}
     */
    public function appendChild(Activity $activity)
    {
        ++ $this->_childrenCount;
        $activity->parent = $this;
        $this->_children[$this->_childrenCount] = $activity;
        return $this->_children[$this->_childrenCount];
    }

    /**
     * {@inheritdoc}
     */
    public function commit($message = null)
    {
        if (!empty($this->_children)) foreach ($this->_children as $activity) {
            $activity->commit($message);
        }
        if (!$this->commitDone) {
            $this->message = $message;
            $this->_activityEndTime = microtime(true);
            $this->_commitStatus = self::STATUS_COMMIT;
        }
        if ($this->_parent) {
            return $this->_parent;
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function rollback($message = null)
    {
        if (!empty($this->_children)) foreach ($this->_children as $activity) {
            $activity->rollback($message);
        }
        if (!$this->commitDone) {
            $this->message = $message;
            $this->_activityEndTime = microtime(true);
            $this->_commitStatus = self::STATUS_ROLLBACK;
        }
        if ($this->_parent) {
            return $this->_parent;
        }
        return null;
    }

    public function trace($level = 0)
    {
        $trace = $this->_traceInternal($level);
        if ($this->_children) {
            $trace['children_self'] = count($this->_children);
            $trace['children_total'] = count($this->_children);

            $trace['time_internal'] = 0;
            foreach ($this->_children as $activity) {
                /** @var static $activity */
                $sub = $activity->trace($level + 1);
                $trace['time_internal'] += $sub['time_total'];
                $trace['children_total'] += $sub['children_total'];
                $trace['children_nodes'][] = $sub;
            }
            $trace['time_clear'] = $trace['time_total'] - $trace['time_internal'];
        } else {
            $trace['time_internal'] = 0;
            $trace['time_clear'] = $trace['time_total'];
        }
        return $trace;
    }

    protected function _traceInternal($level = 0)
    {
        $childrenCount = count($this->_children);
        $totalTime = $this->_activityEndTime - $this->_activityBeginTime;

        return [
            'level'          => $level,
            'tag'            => $this->tag,
            'comment'        => $this->comment,
            'status'         => $this->_commitStatus,
            'message'        => $this->message,
            'data'           => $this->_additionalData,

            'time_start'     => $this->_activityBeginTime,
            'time_end'       => $this->_activityEndTime,

            'time_total'     => $totalTime,
            'time_clear'     => $totalTime,
            'time_internal'  => 0,

            'children_self'  => $childrenCount,
            'children_total' => $childrenCount,

            'children_nodes' => []
        ];
    }
}
