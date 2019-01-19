<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Invocation;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ObjectInvocation extends StaticInvocation
{
    /**
     * @var object
     */
    private $object;

    /**
     * @param string $className
     * @param string $methodName
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
