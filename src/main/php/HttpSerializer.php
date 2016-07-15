<?php
namespace Heneke\Http\Serializer;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpSerializer
{

    /**
     * Deserializes the request, using implicit or explicit conversion.
     *
     * Explicit type conversion is used if a target type is given. Implicit conversion
     * is used if the target type is missing. The target type is then inferred from
     * the content type header using the type registry.
     *
     * @param ServerRequestInterface $serverRequest
     * @param string|null $type the target type
     * @return mixed
     */
    public function deserialize(ServerRequestInterface $serverRequest, $type = null);

    /**
     * @param HttpSerializable $serializable
     * @param ServerRequestInterface $serverRequest
     * @return ResponseInterface
     */
    public function serialize(HttpSerializable $serializable, ServerRequestInterface $serverRequest);
}
