<?php
namespace Heneke\Http\Serializer;

class HttpSerializableResponse implements HttpSerializable
{

    private $data;
    private $format;
    private $mimeType;
    private $groups;

    public function __construct($data, $format = null, $mimeType = null, $groups = null)
    {
        $this->data = $data;
        $this->format = $format;
        $this->mimeType = $mimeType;
        $this->groups = [];
        if ($groups !== null) {
            if (is_array($groups)) {
                $this->groups = $groups;
            } else {
                $this->groups[] = $groups;
            }
        }
    }

    public static function create($data)
    {
        return new HttpSerializableResponse($data);
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

    public function getGroups()
    {
        return $this->groups;
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

    public function withGroups($groups, $default = true)
    {
        if ($default) {
            $this->groups = ['Default'];
        }
        if ($groups !== null) {
            if (is_array($groups)) {
                $this->groups = array_merge($this->groups, $groups);
            } else {
                $this->groups[] = $groups;
            }
        }
        return $this;
    }
}

