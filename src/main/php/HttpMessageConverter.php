<?php
namespace Pixw\HttpMessage\Converter;

use Psr\Http\Message\ServerRequestInterface;

interface HttpMessageConverter
{

    public function deserialize(ServerRequestInterface $request, $type = null);
}
