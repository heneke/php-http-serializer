<?php
namespace Heneke\Http\Serializer;

class SimpleTypeRegistry implements TypeRegistry
{

    /**
     * @var array
     */
    protected $types = [];

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
