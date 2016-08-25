<?php
namespace Heneke\Http\Serializer;

class HttpSerializableResponse implements HttpSerializable
{

    private $data;
    private $format;
    private $mimeType;
    private $groups;

    public function __construct($data, $format = null, $mimeType = null, $groups = null, $withDefaultGroup = true)
    {
        $this->data = $data;
        $this->format = $format;
        $this->mimeType = $mimeType;
        $this->groups = [];
        if ($withDefaultGroup) {
            $this->groups[] = 'Default';
        }
        if ($groups !== null) {
            if (is_array($groups)) {
                $this->groups = array_merge($this->groups, $groups);
            } else {
                $this->groups[] = $groups;
            }
        }
    }

    /**
     * @param $data
     * @return HttpSerializableResponse
     * @codeCoverageIgnore
     */
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

    public function withGroups($groups, $withDefaultGroup = true)
    {
        if (!$groups) {
            throw new \InvalidArgumentException('Groups required!');
        }
        if ($withDefaultGroup) {
            $this->groups = ['Default'];
        } else {
            $this->groups = [];
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

