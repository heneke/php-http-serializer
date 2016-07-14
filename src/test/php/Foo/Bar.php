<?php
namespace Heneke\Http\Serializer\Foo;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class Bar
 * @package Pixw\HttpMessage\Converter\Jms\Foo
 * @XmlRoot(name="bar")
 */
class Bar
{
    /**
     * @var int
     * @Type("integer")
     */
    public $id;
}
