<?php
namespace Heneke\Http\Serializer;

class HttpSerializableResponse implements HttpSerializable
{

    private $data;
    private $format;
    private $mimeType;

    public function __construct($data, $format = null, $mimeType = null)
    {
        $this->data = $data;
        $this->format = $format;
        $this->mimeType = $mimeType;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function withFormat($format)
    {
        if (!$format) {
            throw new \InvalidArgumentException('Format required!');
        }
        $this->format = $format;
        return $this;
    }

    public function withMimeType($mimeType)
    {
        if (!$mimeType) {
            throw new \InvalidArgumentException('Mime type required!');
        }
        $this->mimeType = $mimeType;
        return $this;
    }
}

