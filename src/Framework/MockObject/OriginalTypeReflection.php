<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

final class OriginalTypeReflection implements OriginalType
{
    /**
     * @var \ReflectionClass
     */
    private $class;

    public function __construct(\ReflectionClass $class)
    {
        $this->class = $class;
    }

    public function getCodePrologue(): string
    {
        return '';
    }

    public function getCodeEpilogue(): string
    {
        return '';
    }

    public function hasMethod(string $name): bool
    {
        return $this->class->hasMethod($name);
    }

    /**
     * @throws \OutOfBoundsException if method does not exist
     */
    public function getMethod(string $name): \ReflectionMethod
    {
        try {
            return $this->class->getMethod($name);
        } catch (\ReflectionException $e) {
            throw new \OutOfBoundsException($e->getMessage(), 0, $e);
        }
    }

    public function getMethods(): array
    {
        return $this->class->getMethods();
    }

    public function isInterface(): bool
    {
        return $this->class->isInterface();
    }

    public function getName(): TypeName
    {
        return new TypeName($this->class->getNamespaceName(), $this->class->getShortName());
    }

    public function isFinal(): bool
    {
        return $this->class->isFinal();
    }

    public function implementsInterface(string $interface): bool
    {
        return $this->class->implementsInterface($interface);
    }
}
