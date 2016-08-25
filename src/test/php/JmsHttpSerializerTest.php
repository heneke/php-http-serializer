<?php
namespace Heneke\Http\Serializer;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use JMS\Serializer\SerializerInterface;
use Heneke\Http\Serializer\Foo\Bar;

class JmsHttpSerializerTest extends AbstractHttpSerializerTest
{

    /**
     * @var JmsHttpSerializer
     */
    private $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new JmsHttpSerializer(
            $this->make(SerializerInterface::class),
            $this->make(TypeRegistry::class)
        );
    }

    private function createRequest($contentType, $body = '')
    {
        return new ServerRequest('GET', new Uri('/'), ['Content-Type' => $contentType], $body);
    }

    /**
     * @test
     */
    public function deserializeExplicitObjectJson()
    {
        $mimeType = 'application/json';

        $request = $this->createRequest($mimeType, '{"id":1}');
        $bar = $this->converter->deserialize($request, Bar::class);
        $this->assertNotNull($bar);
        $this->assertTrue($bar instanceof Bar);
        $this->assertEquals(1, $bar->id);
    }

    /**
     * @test
     */
    public function deserializeExplicitArrayJson()
    {
        $mimeType = 'application/json';

        $request = $this->createRequest($mimeType, '[{"id":1}]');
        $bars = $this->converter->deserialize($request, 'array<' . Bar::class . '>');
        $this->assertNotNull($bars);
        $this->assertTrue(is_array($bars));
        $this->assertCount(1, $bars);
        $bar = $bars[0];
        $this->assertTrue($bar instanceof Bar);
        $this->assertEquals(1, $bar->id);
    }

    /**
     * @test
     */
    public function deserializeImplicitObjectJson()
    {
        $mimeType = 'application/vnd.foo.bar';
        $this->make(TypeRegistry::class)->register($mimeType, Bar::class);

        $request = $this->createRequest($mimeType, '{"id":1}');
        $bar = $this->converter->deserialize($request);
        $this->assertNotNull($bar);
        $this->assertTrue($bar instanceof Bar);
        $this->assertEquals(1, $bar->id);

        $request = $this->createRequest($mimeType . '+json', '{"id":1}');
        $bar = $this->converter->deserialize($request);
        $this->assertNotNull($bar);
        $this->assertTrue($bar instanceof Bar);
        $this->assertEquals(1, $bar->id);
    }

    /**
     * @test
     */
    public function deserializeImplicitArrayJson()
    {
        $mimeType = 'application/vnd.foo.bars';
        $this->make(TypeRegistry::class)->register($mimeType, 'array<' . Bar::class . '>');

        $request = $this->createRequest($mimeType, '[{"id":1}]');

        $bars = $this->converter->deserialize($request);
        $this->assertNotNull($bars);
        $this->assertTrue(is_array($bars));
        $this->assertCount(1, $bars);
        $bar = $bars[0];
        $this->assertTrue($bar instanceof Bar);
        $this->assertEquals(1, $bar->id);

        $request = $this->createRequest($mimeType . '+json', '[{"id":1}]');

        $bars = $this->converter->deserialize($request);
        $this->assertNotNull($bars);
        $this->assertTrue(is_array($bars));
        $this->assertCount(1, $bars);
        $bar = $bars[0];
        $this->assertTrue($bar instanceof Bar);
        $this->assertEquals(1, $bar->id);
    }

    /**
     * @test
     */
    public function deserializeImplicitObjectXml()
    {
        $mimeType = 'application/vnd.foo.bar';
        $this->make(TypeRegistry::class)->register($mimeType, Bar::class);

        $request = $this->createRequest($mimeType . '+xml', '<bar><id>1</id></bar>');
        $bar = $this->converter->deserialize($request);
        $this->assertNotNull($bar);
        $this->assertTrue($bar instanceof Bar);
        $this->assertEquals(1, $bar->id);
    }
}