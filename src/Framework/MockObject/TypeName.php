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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TypeName
{
    /**
     * @var ?string
     */
    private $namespaceName;

    /**
     * @var string
     */
    private $simpleName;

    public static function fromQualifiedName(string $fullClassName): self
    {
        if ($fullClassName[0] === '\\') {
            $fullClassName = \substr($fullClassName, 1);
        }

        $classNameParts = \explode('\\', $fullClassName);

        $simpleName    = \array_pop($classNameParts);
        $namespaceName = \implode('\\', $classNameParts);

        return new self($namespaceName, $simpleName);
    }

    public static function fromReflection(\ReflectionClass $type): self
    {
        return new self(
            $type->getNamespaceName(),
            $type->getShortName()
        );
    }

    public function __construct(?string $namespaceName, string $simpleName)
    {
        if ($namespaceName === '') {
            $namespaceName = null;
        }
        $this->namespaceName  = $namespaceName;
        $this->simpleName     = $simpleName;
    }

    public function getNamespaceName(): ?string
    {
        return $this->namespaceName;
    }

    public function getSimpleName(): string
    {
        return $this->simpleName;
    }

    public function getQualifiedName(): string
    {
        return $this->isNamespaced()
             ? $this->namespaceName . '\\' . $this->simpleName
             : $this->simpleName;
    }

    public function isNamespaced(): bool
    {
        return $this->namespaceName !== null;
    }
}
