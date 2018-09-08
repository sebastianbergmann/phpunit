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

final class OriginalTypeGenerated implements OriginalType
{
    /**
     * @var TypeName
     */
    private $originalType;

    public function __construct(TypeName $originalType)
    {
        $this->originalType = $originalType;
    }

    public function getCodePrologue(): string
    {
        $prologue = 'class ' . $this->originalType->getSimpleName() . "\n{\n}\n\n";

        if ($this->originalType->isNamespaced()) {
            $prologue = 'namespace ' . $this->originalType->getNamespaceName() .
                " {\n\n" . $prologue . "}\n\n" .
                "namespace {\n\n";
        }

        return $prologue;
    }

    public function getCodeEpilogue(): string
    {
        if ($this->originalType->isNamespaced()) {
            return "\n\n}";
        }

        return '';
    }

    public function hasMethod(string $name): bool
    {
        return false;
    }

    /**
     * @throws \OutOfBoundsException if method does not exist
     */
    public function getMethod(string $name): \ReflectionMethod
    {
        throw new \OutOfBoundsException('Generated types have no methods.');
    }

    public function getMethods(): array
    {
        return [];
    }

    public function isInterface(): bool
    {
        return false;
    }

    public function getName(): TypeName
    {
        return $this->originalType;
    }

    public function isFinal(): bool
    {
        return false;
    }

    public function implementsInterface(string $interface): bool
    {
        return false;
    }
}
