<?php


class ProfilerTest extends \PHPUnit\Framework\TestCase
{
    public function testComponentInitialisation()
    {
        $this->assertInstanceOf(\cvsoft\profiler\Component::class, Yii::$app->get('profiler'));
    }

    public function testConstTiming()
    {
        $this->assertEquals(DEFINED_CONST, Yii::$app->profiler->beginTime);
    }

    public function testFakeProfilerInit()
    {
        $this->assertTrue(Yii::$app->profilerFake->beginTime > 0);
    }

    public function testConstTimeIsEarlier()
    {
        $this->assertTrue(Yii::$app->profiler->beginTime < Yii::$app->profilerFake->beginTime);
    }
}
