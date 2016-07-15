<?php
namespace Heneke\Http\Serializer;

interface HttpSerializable
{

    public function getData();

    public function getFormat();

    public function getMimeType();

    public function getGroups();

    public function withFormat($format);

    public function withMimeType($mimeType);

    public function withGroups($groups, $default = true);
}