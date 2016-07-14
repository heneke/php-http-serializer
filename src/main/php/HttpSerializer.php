<?php
namespace Heneke\Http\Serializer;

use Psr\Http\Message\ServerRequestInterface;

interface HttpSerializer
{

    public function deserialize(ServerRequestInterface $serverRequest, $type = null);
}

