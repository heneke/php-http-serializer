<?php
namespace Pixw\HttpMessage;

use Doctrine\Common\Annotations\AnnotationRegistry;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializerBuilder;
use Pixw\HttpMessage\Converter\TypeRegistry;

abstract class AbstractHttpMessageConverterTest extends \PHPUnit_Framework_TestCase
{

    private $registry = [];
    private $cache = [];

    protected function setUp()
    {
        parent::setUp();

        $this->register(SerializerInterface::class, function () {
            AnnotationRegistry::registerLoader('class_exists');
            return SerializerBuilder::create()
                ->setDebug(true)
                ->setPropertyNamingStrategy(new IdenticalPropertyNamingStrategy())
                ->build();
        });

        $this->register(TypeRegistry::class, function () {
            return new TypeRegistry();
        });
    }

    protected function make($serviceId)
    {
        if (!$serviceId) {
            throw \InvalidArgumentException('Service-ID required!');
        }
        if (!isset($this->cache[$serviceId])) {
            if (!isset($this->registry[$serviceId])) {
                throw new \InvalidArgumentException('Service-ID ' . $serviceId . ' is not registered!');
            }
            $this->cache[$serviceId] = call_user_func($this->registry[$serviceId]);
        }
        return $this->cache[$serviceId];
    }

    protected function register($serviceId, callable $callback)
    {
        if (!$serviceId) {
            throw \InvalidArgumentException('Service-ID required!');
        }
        if (isset($this->registry[$serviceId])) {
            throw \InvalidArgumentException('Service-ID ' . $serviceId . ' is already registered!');
        }
        $this->registry[$serviceId] = $callback;
    }
}