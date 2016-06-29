<?php
namespace Pixw\HttpMessage\Converter;

class TypeRegistry
{

    private $types = [];

    public function register($mimeType, $type)
    {
        $mimeType = $this->normalize($mimeType);

        if (!$mimeType) {
            throw new \InvalidArgumentException('Mime type required!');
        }
        if (!$type) {
            throw new \InvalidArgumentException('Type required!');
        }
        if (isset($this->types[$mimeType])) {
            throw new \InvalidArgumentException("{$mimeType} is already registered!");
        }

        $this->types[$mimeType] = $type;
    }

    private function normalize($input)
    {
        return trim(strtolower($input));
    }

    public function resolve($mimeType)
    {
        $mimeType = $this->normalize($mimeType);
        if (!$mimeType) {
            throw new \InvalidArgumentException('Mime type required!');
        }
        if (!isset($this->types[$mimeType])) {
            throw new \InvalidArgumentException("Mime type {$mimeType} is not registered!");
        }

        return $this->types[$mimeType];
    }
}