<?php
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
