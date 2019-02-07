<?php
namespace cvsoft\profiler;


class Trace extends \yii\base\Component
{
    public $log = [];
    private $_table    = null;
    private $_tags     = null;
    private $_timeline = null;

    private $_row = 0;

    public function getTimingTable()
    {
        if (null === $this->_table) {
            $this->_table = [];
            $this->_addNodeTiming($this->log);
        }
        return $this->_table;
    }

    public function getTimeLine()
    {
        if (null === $this->_timeline) {
            $this->_timeline = [];

            foreach ($this->getTimingTable() as $row) {
                $this->_timeline[(string)$row['time_start']] = [
                    'action'=>'begin', 'id' => $row['id'], 'tag' => $row['tag'], 'comment' => $row['comment'], 'level' => $row['level'],
                    'message' => 'begin #' . $row['id'] . ' (' . $row['tag'] . ') ' . $row['comment']
                ];
                $this->_timeline[(string)$row['time_end']] = [
                    'action'  => (10 == $row['status']) ? 'commit' : 'rollback', 'id' => $row['id'], 'message' => $row['message'], 'level' => $row['level'],
                    'message' => ((10 == $row['status']) ? 'commit' : 'rollback') . ' #' . $row['id'] . ' ' . $row['message']
                ];
            }
            ksort($this->_timeline);
        }
        return $this->_timeline;
    }

    public function getTimeLineString($rowDelimiter = "\n", $levelDelimiter = "--")
    {
        $content = '';
        $content .= $rowDelimiter;
        foreach ($this->getTimeLine() as $row) {
            $content .= $rowDelimiter . str_repeat($levelDelimiter, $row['level'] + 1) . ' ' . $row['message'];
        }
        return $content;
    }

    public function getTagTiming()
    {
        if (null === $this->_tags) {
            $this->_tags = [];
            $table = $this->timingTable;
            $total = 0;

            $ft = ['time' => 0, 'percent' => 0, 'count' => 0];

            foreach ($table as $row) {
                if (!isset($this->_tags[$row['tag']])) $this->_tags[$row['tag']] = ['time' => 0, 'count' => 0, 'percent' => 0];
                ++$this->_tags[$row['tag']]['count'];
                ++$ft['count'];

                $this->_tags[$row['tag']]['time'] += $row['time_clear'];
                $total += $row['time_clear'];
                $ft['time'] += $row['time_clear'];
            }
            foreach ($this->_tags as $k=>$v) {
                $this->_tags[$k]['percent'] = $v['time'] / $total * 100;
                $this->_tags[$k]['percent_round'] = round($this->_tags[$k]['percent']);
                $ft['percent'] += $this->_tags[$k]['percent_round'];
            }
            if (100 !== $ft['percent']) {
                $delta = 100 - $ft['percent'];
                $this->_tags[$k]['percent'] += $delta;
                $ft['percent'] = 100;
            }
            $this->_tags['__total'] = $ft;

        }
        return $this->_tags;
    }

    private function _addNodeTiming($tree)
    {
        ++ $this->_row;
        $row = [
            'id'    => $this->_row,
            'level' => $tree['level'],
            'tag' => $tree['tag'],
            'time_start' => $tree['time_start'],
            'time_total' => $tree['time_total'],
            'time_clear' => $tree['time_clear'],
            'time_end'   => $tree['time_end'],
            'comment'    => $tree['comment'],
            'status'     => $tree['status'],
            'message'    => $tree['message'],
        ];
        $this->_table[$this->_row] = $row;
        if ($tree['children_nodes']) foreach ($tree['children_nodes'] as $node) {
            $this->_addNodeTiming($node);
        }
    }
}
