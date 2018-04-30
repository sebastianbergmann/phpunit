<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Invocation;

/**
 * Represents a non-static invocation.
 */
class ObjectInvocation extends StaticInvocation
{
    /**
     * @var object
     */
    private $object;

    /**
     * @param string $className
     * @param string $methodName
     * @param array  $parameters
     * @param string $returnType
     * @param object $object
     * @param bool   $cloneObjects
     */
    public function __construct($className, $methodName, array $parameters, $returnType, $object, $cloneObjects = false)
    {
        parent::__construct($className, $methodName, $parameters, $returnType, $cloneObjects);

        $this->object = $object;
    }

    public function getObject()
    {
        return $this->object;
    }
}
