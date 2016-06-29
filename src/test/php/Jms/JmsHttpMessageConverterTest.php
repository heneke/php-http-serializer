<?php
namespace Pixw\HttpMessage\Converter\Jms;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use Pixw\HttpMessage\AbstractHttpMessageConverterTest;
use JMS\Serializer\SerializerInterface;
use Pixw\HttpMessage\Converter\Jms\Foo\Bar;
use Pixw\HttpMessage\Converter\TypeRegistry;

class JmsHttpMessageConverterHttpMessageConverterTest extends AbstractHttpMessageConverterTest
{

    /**
     * @var JmsHttpMessageConverter
     */
    private $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new JmsHttpMessageConverter(
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
    public function explicitObjectJson()
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
    public function explicitArrayJson()
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
    public function implicitObjectJson()
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
    public function implicitArrayJson()
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
    public function implicitObjectXml()
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