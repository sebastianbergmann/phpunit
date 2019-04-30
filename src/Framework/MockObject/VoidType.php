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
class VoidType extends Type
{
    public function isAssignable(Type $other): bool
    {
        return $other instanceof self;
    }

    public function getReturnTypeDeclaration(): string
    {
        return ': void';
    }

    public function allowsNull(): bool
    {
        return false;
    }
}
