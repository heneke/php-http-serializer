<?php
namespace Heneke\Http\Serializer;

class HttpSerializableResponseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function createScalarGroupWithoutDefaultAndModify()
    {
        $r = new HttpSerializableResponse('foo', 'json', 'application/json', 'group1', false);
        $this->assertEquals('foo', $r->getData());
        $this->assertEquals('json', $r->getFormat());
        $this->assertEquals('application/json', $r->getMimeType());
        $this->assertCount(1, $r->getGroups());
        $this->assertEquals('group1', $r->getGroups()[0]);

        $r->withFormat('xml');
        $this->assertEquals('xml', $r->getFormat());
        $r->withMimeType('application/xml', $r->getFormat());
        $r->withGroups('group2', false);
        $this->assertCount(1, $r->getGroups());
        $this->assertEquals('group2', $r->getGroups()[0]);
        $r->withGroups(['group3', 'group4'], true);
        $this->assertCount(3, $r->getGroups());
        $this->assertEquals('Default', $r->getGroups()[0]);
        $this->assertEquals('group3', $r->getGroups()[1]);
        $this->assertEquals('group4', $r->getGroups()[2]);
    }

    /**
     * @test
     */
    public function createArrayGroups()
    {
        $r = new HttpSerializableResponse('foo', 'json', 'application/json', ['group1', 'group2']);
        $this->assertEquals('foo', $r->getData());
        $this->assertEquals('json', $r->getFormat());
        $this->assertEquals('application/json', $r->getMimeType());
        $this->assertCount(3, $r->getGroups());
        $this->assertEquals('Default', $r->getGroups()[0]);
        $this->assertEquals('group1', $r->getGroups()[1]);
        $this->assertEquals('group2', $r->getGroups()[2]);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function modifierInvalidFormat()
    {
        HttpSerializableResponse::create('foo')->withFormat('');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function modifierInvalidMime()
    {
        HttpSerializableResponse::create('foo')->withMimeType('');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function modifierInvalidGroups()
    {
        HttpSerializableResponse::create('foo')->withGroups('');
    }
}
