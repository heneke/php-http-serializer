<?php
namespace Pixw\HttpMessage\Converter\Jms;

use JMS\Serializer\SerializerInterface;
use Pixw\HttpMessage\Converter\HttpMessageConverter;
use Pixw\HttpMessage\Converter\TypeRegistry;
use Psr\Http\Message\ServerRequestInterface;

class JmsHttpMessageConverter implements HttpMessageConverter
{

    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';
    const FORMAT_YML = 'yml';

    private static $regexWithFormat = '/(.+)\+(.*)/i';
    private static $serializableFormats = [self::FORMAT_JSON, self::FORMAT_XML, self::FORMAT_YML];
    private static $deserializableFormats = [self::FORMAT_JSON, self::FORMAT_XML];

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var TypeRegistry
     */
    private $typeRegistry;

    /**
     * @var string
     */
    private $defaultDeserializationFormat;

    public function __construct(SerializerInterface $serializer, TypeRegistry $typeRegistry, $defaultDeserializationFormat = self::FORMAT_JSON)
    {
        $this->serializer = $serializer;
        $this->typeRegistry = $typeRegistry;
        $this->defaultDeserializationFormat = $this->normalize($defaultDeserializationFormat);

        if (!$this->defaultDeserializationFormat) {
            throw new \InvalidArgumentException('Default deserialization format required!');
        }
        $this->checkFormat($this->defaultDeserializationFormat);
    }

    public function deserialize(ServerRequestInterface $request, $type = null)
    {
        if (!$type) {
            return $this->convertImplicit($request, $type);
        }
        return $this->convertExcplicit($request, $type);
    }

    private function convertImplicit(ServerRequestInterface $request)
    {
        $mimeTypeAndFormat = $this->resolveMimeTypeAndFormat($request);
        $type = $this->typeRegistry->resolve($mimeTypeAndFormat['mimeType']);
        return $this->convertInternal($request, $type, $mimeTypeAndFormat['format']);
    }

    private function convertExcplicit(ServerRequestInterface $request, $type)
    {
        $format = $this->resolveFormat($request);
        return $this->convertInternal($request, $type, $format);
    }

    private function convertInternal(ServerRequestInterface $request, $type, $format)
    {
        if (!$type) {
            throw new \InvalidArgumentException('Type required!');
        }
        if (!$format) {
            throw new \InvalidArgumentException('Format required!');
        }

        return $this->serializer->deserialize($request->getBody()->getContents(), $type, $format);
    }

    private function resolveMimeTypeAndFormat(ServerRequestInterface $request)
    {
        if (!$request->hasHeader('Content-Type')) {
            throw new \InvalidArgumentException('Content-Type header is missing!');
        }

        $mimeTypeAndFormat = ['mimeType' => null, 'format' => $this->defaultDeserializationFormat];
        $contentType = $request->getHeaderLine('Content-Type');

        $matches = [];
        if (preg_match(self::$regexWithFormat, $contentType, $matches)) {
            $requestedFormat = $this->normalize($matches[2]);
            $this->checkFormat($requestedFormat);
            $mimeTypeAndFormat['mimeType'] = $matches[1];
            $mimeTypeAndFormat['format'] = $requestedFormat;
        } else {
            $mimeTypeAndFormat['mimeType'] = $contentType;
        }

        return $mimeTypeAndFormat;
    }

    private function resolveFormat(ServerRequestInterface $request)
    {
        if (!$request->hasHeader('Content-Type')) {
            throw new \InvalidArgumentException('Content-Type header is missing!');
        }

        $contentType = $this->normalize($request->getHeaderLine('Content-Type'));
        switch ($contentType) {
            case 'application/json':
                return self::FORMAT_JSON;
            case 'application/xml':
                return self::FORMAT_XML;
            case 'application/yml':
            case 'application/yaml':
                return self::FORMAT_YML;
            default:
                return $this->defaultDeserializationFormat;
        }
    }

    private function normalize($input)
    {
        return trim(strtolower($input));
    }

    private function checkFormat($format)
    {
        if (!in_array($format, self::$deserializableFormats)) {
            throw new \InvalidArgumentException("{$format} is not a supported deserialization format!");
        }
    }
}
