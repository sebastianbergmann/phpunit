<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ClassWithAllPossibleReturnTypes
{
    public function methodWithNoReturnTypeDeclaration()
    {
    }

    public function methodWithVoidReturnTypeDeclaration(): void
    {
    }

    public function methodWithStringReturnTypeDeclaration(): string
    {
        return 'string';
    }

    public function methodWithFloatReturnTypeDeclaration(): float
    {
        return 1.0;
    }

    public function methodWithIntReturnTypeDeclaration(): int
    {
        return 1;
    }

    public function methodWithBoolReturnTypeDeclaration(): bool
    {
        return true;
    }

    public function methodWithArrayReturnTypeDeclaration(): array
    {
        return ['string'];
    }

    public function methodWithTraversableReturnTypeDeclaration(): Traversable
    {
        return new ArrayIterator(['string']);
    }

    public function methodWithGeneratorReturnTypeDeclaration(): Generator
    {
        yield 1;
    }

    public function methodWithObjectReturnTypeDeclaration(): object
    {
        return new Exception;
    }

    public function methodWithClassReturnTypeDeclaration(): stdClass
    {
        return new stdClass;
    }
}
