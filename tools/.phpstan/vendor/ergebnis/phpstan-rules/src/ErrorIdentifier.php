<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2025 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/phpstan-rules
 */

namespace Ergebnis\PHPStan\Rules;

/**
 * @internal
 */
final class ErrorIdentifier
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function declareStrictTypes(): self
    {
        return new self('declareStrictTypes');
    }

    public static function final(): self
    {
        return new self('final');
    }

    public static function finalInAbstractClass(): self
    {
        return new self('finalInAbstractClass');
    }

    public static function invokeParentHookMethod(): self
    {
        return new self('invokeParentHookMethod');
    }

    public static function noCompact(): self
    {
        return new self('noCompact');
    }

    public static function noConstructorParameterWithDefaultValue(): self
    {
        return new self('noConstructorParameterWithDefaultValue');
    }

    public static function noAssignByReference(): self
    {
        return new self('noAssignByReference');
    }

    public static function noErrorSuppression(): self
    {
        return new self('noErrorSuppression');
    }

    public static function noEval(): self
    {
        return new self('noEval');
    }

    public static function noExtends(): self
    {
        return new self('noExtends');
    }

    public static function noIsset(): self
    {
        return new self('noIsset');
    }

    public static function noNamedArgument(): self
    {
        return new self('noNamedArgument');
    }

    public static function noParameterPassedByReference(): self
    {
        return new self('noParameterPassedByReference');
    }

    public static function noParameterWithContainerTypeDeclaration(): self
    {
        return new self('noParameterWithContainerTypeDeclaration');
    }

    public static function noParameterWithNullDefaultValue(): self
    {
        return new self('noParameterWithNullDefaultValue');
    }

    public static function noParameterWithNullableTypeDeclaration(): self
    {
        return new self('noParameterWithNullableTypeDeclaration');
    }

    public static function noNullableReturnTypeDeclaration(): self
    {
        return new self('noNullableReturnTypeDeclaration');
    }

    public static function noReturnByReference(): self
    {
        return new self('noReturnByReference');
    }

    public static function noSwitch(): self
    {
        return new self('noSwitch');
    }

    public static function privateInFinalClass(): self
    {
        return new self('privateInFinalClass');
    }

    public static function testCaseWithSuffix(): self
    {
        return new self('testCaseWithSuffix');
    }

    public function toString(): string
    {
        return \sprintf(
            'ergebnis.%s',
            $this->value,
        );
    }
}
