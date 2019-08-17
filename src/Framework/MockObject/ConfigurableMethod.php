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
     * @var string
     */
    private $name;

    /**
     * @var Type
     */
    private $returnType;

    public function __construct(string $name, Type $returnType)
    {
        $this->name       = $name;
        $this->returnType = $returnType;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function mayReturn($value): bool
    {
        if ($value === null && $this->returnType->allowsNull()) {
            return true;
        }

        return $this->returnType->isAssignable(Type::fromValue($value, false));
    }

    public function getReturnTypeDeclaration(): string
    {
        return $this->returnType->getReturnTypeDeclaration();
    }
}
