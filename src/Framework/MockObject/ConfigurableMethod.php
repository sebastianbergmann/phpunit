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

use SebastianBergmann\Type\Type;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ConfigurableMethod
{
    /**
     * @psalm-var non-empty-string
     */
    private readonly string $name;
    private readonly Type $returnType;

    /**
     * @psalm-param non-empty-string $name
     */
    public function __construct(string $name, Type $returnType)
    {
        $this->name       = $name;
        $this->returnType = $returnType;
    }

    /**
     * @psalm-return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    public function mayReturn(mixed $value): bool
    {
        return $this->returnType->isAssignable(Type::fromValue($value, false));
    }

    public function returnTypeDeclaration(): string
    {
        return $this->returnType->asString();
    }
}
