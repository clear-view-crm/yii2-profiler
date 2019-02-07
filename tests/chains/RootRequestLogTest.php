<?php

class RootRequestLogTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \cvsoft\profiler\Component
     */
    private $_profiler = null;

    public function setUp()
    {
        $this->_profiler = Yii::$app->get('profiler');
        $this->_profiler->enable();
    }

    public function tearDown()
    {
        $this->_profiler->destroy();
        $this->_profiler = null;
    }

    public function testRootActivityClass()
    {
        $this->assertInstanceOf(
            \cvsoft\profiler\activities\WebRequestActivity::class,
            $this->_profiler->root
        );
    }

    public function testRootPointer()
    {
        $this->assertTrue($this->_profiler->isRoot);
    }

    public function testRootAfterAppend()
    {
        $ca = $this->_profiler->append();
        print_r($ca);
        print_r($this->_profiler->root);
        $this->assertFalse($this->_profiler->isRoot);
    }
}
