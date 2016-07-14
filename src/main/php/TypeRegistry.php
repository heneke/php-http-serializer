<?php
namespace Heneke\Http\Serializer;

interface TypeRegistry
{

    public function register($mimeType, $type);

    public function resolve($mimeType);
}
