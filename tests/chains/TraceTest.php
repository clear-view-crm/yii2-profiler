<?php

class TraceTest extends \PHPUnit\Framework\TestCase
{
    private $_profiler;
    /** @var \cvsoft\profiler\Trace */
    private $_trace;

    public function setUp()
    {
        $this->_profiler = Yii::$app->get('profiler');
        $this->_profiler->enable();
        $this->_doActivities();
        $this->_trace = $this->_profiler->trace();
    }

    public function tearDown()
    {
        $this->_trace = null;
    }

    private function _doActivities()
    {
        $this->pause();
        $this->_profiler->append('g3');
            $this->pause(20000);
        $this->_profiler->commit();
        $this->pause();
        $this->_profiler->append('g3');
            $this->pause(30000);
        $this->_profiler->commit();
        $this->_profiler->append('sub');
            $this->pause();
            $this->_profiler->append('g1');
                $this->pause();
                $this->_profiler->append('g2');
                $this->pause();
                    $this->_profiler->append('g3');
                    $this->pause();
                    $this->_profiler->commit();
                $this->pause();
            $this->_profiler->append('g3');
                $this->pause();
            $this->_profiler->commit();
        $this->_profiler->commit();

        $this->_profiler->commit();
        $this->pause();
        $this->_profiler->commit();
        $this->_profiler->commit();
    }

    public function testTags()
    {
        $timing = $this->_trace->tagTiming;
        $total = $timing['__total'] ?? ['time' => 0, 'percent' => 100, 'count' => 1];
        $this->assertEquals(100, $total['percent']);
    }

    public function testTimeLine()
    {
        $timing = $this->_trace->timeLine;

        $correct = true;
        $_pre = 0;
        foreach ($timing as $k=>$item) {
            if ($k > $_pre) {

                $_pre = $k;
            } else {
                $correct = false;
                break;
            }
        }
        $this->assertTrue($correct);
    }

    protected function pause($l = 10000)
    {
        for($i = 0; $i < $l; $i++) {
        }
    }
}
