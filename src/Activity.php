<?php
namespace cvsoft\profiler;

/**
 * Interface Activity
 *
 * @property-write float $beginTime время начала выполнения действия.
 *     Свойство можнт быть установлено только ДО момента инициализации записи методом [[init()]]
 * @property-read bool $commitDone флаг выполнения [[commit()] или [[rollback()]] для заданной записи
 * @property-write Activity $parent
 *
 *
 * @package cvsoft\profiler
 */
interface Activity
{
    const STATUS_NONE = 0;
    const STATUS_COMMIT = 10;
    const STATUS_ROLLBACK = 20;

    /**
     * Фиксирует время выполнения действия
     * @return Activity
     */
    public function commit();

    /**
     * Создает дочернюю запись лога в данной и возвращает ее
     * @param Activity $activity
     * @return Activity
     */
    public function appendChild(Activity $activity);
}
