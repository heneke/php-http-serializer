<?php
namespace Heneke\Http\Serializer;

use GuzzleHttp\Psr7\Response;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Negotiation\Negotiator;
use Psr\Http\Message\ServerRequestInterface;

class JmsHttpSerializer implements HttpSerializer
{

    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';
    const FORMAT_YML = 'yml';

    private static $regexWithFormat = '/(.+)\+(.*)/i';
    private static $serializableFormats = [self::FORMAT_JSON, self::FORMAT_XML, self::FORMAT_YML];
    private static $deserializableFormats = [self::FORMAT_JSON, self::FORMAT_XML];
    private static $priorities = ['application/json', 'application/xml', 'application/yml', 'application/yaml'];

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var TypeRegistry
     */
    private $typeRegistry;

    /**
     * @var Negotiator
     */
    private $negotiator;

    /**
     * @var string
     */
    private $defaultDeserializationFormat;

    /**
     * @var string
     */
    private $defaultSerializationFormat;

    public function __construct(SerializerInterface $serializer, TypeRegistry $typeRegistry, $defaultDeserializationFormat = self::FORMAT_JSON, $defaultSerializationFormat = self::FORMAT_JSON)
    {
        $this->serializer = $serializer;
        $this->typeRegistry = $typeRegistry;
        $this->negotiator = new Negotiator();

        $this->defaultDeserializationFormat = $this->normalize($defaultDeserializationFormat);
        if (!$this->defaultDeserializationFormat) {
            throw new \InvalidArgumentException('Default deserialization format required!');
        }
        $this->checkDeserializationFormat($this->defaultDeserializationFormat);

        $this->defaultSerializationFormat = $this->normalize($defaultSerializationFormat);
        if (!$this->defaultSerializationFormat) {
            throw new \InvalidArgumentException('Default serialization format required!');
        }
        $this->checkSerializationFormat($this->defaultSerializationFormat);
    }

    public function deserialize(ServerRequestInterface $serverRequest, $type = null)
    {
        if (!$type) {
            return $this->deserializeImplicit($serverRequest, $type);
        }
        return $this->deserializeExcplicit($serverRequest, $type);
    }

    private function deserializeImplicit(ServerRequestInterface $request)
    {
        $mimeTypeAndFormat = $this->resolveMimeTypeAndFormatForRequest($request);
        $type = $this->typeRegistry->resolve($mimeTypeAndFormat['mimeType']);
        return $this->deserializeInternal($request, $type, $mimeTypeAndFormat['format']);
    }

    private function deserializeExcplicit(ServerRequestInterface $request, $type)
    {
        $format = $this->resolveFormatForRequest($request);
        return $this->deserializeInternal($request, $type, $format);
    }

    private function deserializeInternal(ServerRequestInterface $request, $type, $format)
    {
        if (!$type) {
            throw new \InvalidArgumentException('Type required!');
        }
        if (!$format) {
            throw new \InvalidArgumentException('Format required!');
        }

        return $this->serializer->deserialize($request->getBody()->getContents(), $type, $format);
    }

    private function resolveMimeTypeAndFormatForRequest(ServerRequestInterface $request)
    {
        if (!$request->hasHeader('Content-Type')) {
            throw new \InvalidArgumentException('Content-Type header is missing!');
        }

        $mimeTypeAndFormat = ['mimeType' => null, 'format' => $this->defaultDeserializationFormat];
        $contentType = $request->getHeaderLine('Content-Type');

        $matches = [];
        if (preg_match(self::$regexWithFormat, $contentType, $matches)) {
            $requestedFormat = $this->normalize($matches[2]);
            $this->checkDeserializationFormat($requestedFormat);
            $mimeTypeAndFormat['mimeType'] = $matches[1];
            $mimeTypeAndFormat['format'] = $requestedFormat;
        } else {
            $mimeTypeAndFormat['mimeType'] = $contentType;
        }

        return $mimeTypeAndFormat;
    }

    private function resolveFormatForRequest(ServerRequestInterface $request)
    {
        if (!$request->hasHeader('Content-Type')) {
            throw new \InvalidArgumentException('Content-Type header is missing!');
        }

        $mimeType = $this->normalize($request->getHeaderLine('Content-Type'));
        return $this->resolveFormatForMimeType($mimeType, $this->defaultDeserializationFormat);
    }

    private function resolveMimeTypeForResponse(ServerRequestInterface $request)
    {
        if (!$request->hasHeader('Accept')) {
            throw new \InvalidArgumentException('Accept header is missing!');
        }

        return $this->negotiator->getBest($request->getHeaderLine('Accept'), self::$priorities)->getValue();
    }

    private function resolveFormatForMimeType($mimeType, $default)
    {
        switch ($mimeType) {
            case 'application/json':
                return self::FORMAT_JSON;
            case 'application/xml':
                return self::FORMAT_XML;
            case 'application/yml':
            case 'application/yaml':
                return self::FORMAT_YML;
            default:
                return $default;
        }
    }

    private function resolveMimeTypeForFormat($format, $default)
    {
        switch ($format) {
            case self::FORMAT_JSON:
                return 'application/json';
            case self::FORMAT_XML:
                return 'application/xml';
            case self::FORMAT_YML:
                return 'application/yml';
            default:
                return $default;
        }
    }

    private function normalize($input)
    {
        return trim(strtolower($input));
    }

    private function checkDeserializationFormat($format)
    {
        if (!in_array($format, self::$deserializableFormats)) {
            throw new \InvalidArgumentException("{$format} is not a supported deserialization format!");
        }
    }

    private function checkSerializationFormat($format)
    {
        if (!in_array($format, self::$serializableFormats)) {
            throw new \InvalidArgumentException("{$format} is not a supported serialization format!");
        }
    }

    public function serialize(HttpSerializable $serializable, ServerRequestInterface $request)
    {
        if ($serializable->getFormat()) {
            return $this->serializeExplicit($serializable);
        }
        return $this->serializeImplicit($serializable, $request);
    }

    private function serializeImplicit(HttpSerializable $serializable, ServerRequestInterface $request)
    {
        $mimeType = $this->resolveMimeTypeForResponse($request);
        $format = $this->resolveFormatForMimeType($mimeType, $this->defaultSerializationFormat);
        return $this->serializeInternal($serializable, $format, $mimeType);
    }

    private function serializeExplicit(HttpSerializable $serializable)
    {
        return $this->serializeInternal($serializable, $serializable->getFormat(), $serializable->getMimeType());
    }

    private function serializeInternal(HttpSerializable $serializable, $format, $mimeType = null)
    {
        if (!$format) {
            throw new \InvalidArgumentException('Format required!');
        }

        $format = $this->normalize($format);
        $this->checkSerializationFormat($format);

        if ($mimeType == null) {
            $mimeType = $this->resolveMimeTypeForFormat($format, $this->defaultSerializationFormat);
        }
        if (!in_array($mimeType, self::$priorities)) {
            if (!preg_match(self::$regexWithFormat, $mimeType)) {
                $mimeType = $mimeType . '+' . $format;
            }
        }

        if (!empty($serializable->getGroups())) {
            $serialized = $this->serializer->serialize($serializable->getData(), $format, SerializationContext::create()->setGroups($serializable->getGroups()));
        } else {
            $serialized = $this->serializer->serialize($serializable->getData(), $format);
        }

        return new Response(200, ['Content-Type' => $mimeType], $serialized);
    }
}
