<?php
namespace Heneke\Http\Serializer\Handler;

use Carbon\Carbon;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\VisitorInterface;
use Mockery as m;

class CarbonHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var CarbonHandler
     */
    private $handler;

    /**
     * @before
     */
    public function before()
    {
        $this->handler = new CarbonHandler();
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * @test
     */
    public function serializeSameTz()
    {
        $date = Carbon::create(2000, 10, 23, 11, 12, 13, $this->handler->getDefaultTimezone());
        $expectedString = $date->format($this->handler->getDefaultFormat());
        $visitor = m::mock(VisitorInterface::class);
        $visitor->shouldReceive('visitString')->once()->andReturnUsing(function ($input) {
            return $input;
        });
        $serialized = $this->handler->serializeCarbon($visitor, $date, [], SerializationContext::create());
        $this->assertEquals($expectedString, $serialized);
    }

    /**
     * @test
     */
    public function serializeDifferentTz()
    {
        $date = Carbon::create(2000, 10, 23, 11, 12, 13, new \DateTimeZone('Europe/Athens'));
        $expected = $date->setTimezone($this->handler->getDefaultTimezone());
        $expectedString = $expected->format($this->handler->getDefaultFormat());
        $this->assertEquals('2000-10-23T08:12:13Z', $expectedString);
        $visitor = m::mock(VisitorInterface::class);
        $visitor->shouldReceive('visitString')->once()->andReturnUsing(function ($input) {
            return $input;
        });
        $serialized = $this->handler->serializeCarbon($visitor, $date, [], SerializationContext::create());
        $this->assertEquals($expectedString, $serialized);
    }

    /**
     * @test
     */
    public function deserializeSameTz()
    {
        $date = Carbon::create(2000, 10, 23, 11, 12, 13, $this->handler->getDefaultTimezone());
        $dateString = $date->format('Y-m-d\TH:i:s\Z');
        $visitor = m::mock(VisitorInterface::class);
        $actual = $this->handler->deserializeCarbon($visitor, $dateString, []);
        $this->assertNotNull($actual);
        $this->assertEquals($actual->getTimestamp(), $date->getTimestamp());
    }

    /**
     * @test
     */
    public function deserializeDifferentTz()
    {
        $date = Carbon::create(2000, 10, 23, 11, 12, 13, new \DateTimeZone('Europe/Athens'));
        $input = $date->setTimezone($this->handler->getDefaultTimezone());
        $inputString = $input->format($this->handler->getDefaultFormat());
        $this->assertEquals('2000-10-23T08:12:13Z', $inputString);
        $visitor = m::mock(VisitorInterface::class);
        $actual = $this->handler->deserializeCarbon($visitor, $inputString, []);
        $this->assertNotNull($actual);
        $this->assertEquals($actual->getTimestamp(), $input->getTimestamp());
    }

    /**
     * @test
     */
    public function deserializeNull()
    {
        $visitor = m::mock(VisitorInterface::class);
        $this->assertNull($this->handler->deserializeCarbon($visitor, null, []));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Default format
     */
    public function createInvalidFormat()
    {
        new CarbonHandler('', 'UTC');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Default timezone
     */
    public function createInvalidTz()
    {
        new CarbonHandler('c', '');
    }
}
