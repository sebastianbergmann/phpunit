<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tokenizer;

/**
 * Forward Compatibility Tokens.
 *
 * Class containing tokens that are not present in the lowest supported PHP version,
 * so the code can always use the class constant, instead of checking if the constant is defined
 *
 * @TODO PHP 8.0+, when mentioned PHP version is required, remove the related consts
 * @TODO PHP 8.1+, when mentioned PHP version is required, remove the related consts
 * @TODO PHP 8.4+, when mentioned PHP version is required, remove the related consts
 * @TODO PHP 8.5+, when mentioned PHP version is required, remove the related consts
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FCT
{
    // PHP 8.0+
    public const T_ATTRIBUTE = \PHP_VERSION_ID >= 8_00_00 ? \T_ATTRIBUTE : -801;
    public const T_MATCH = \PHP_VERSION_ID >= 8_00_00 ? \T_MATCH : -802;
    public const T_NULLSAFE_OBJECT_OPERATOR = \PHP_VERSION_ID >= 8_00_00 ? \T_NULLSAFE_OBJECT_OPERATOR : -803;
    public const T_NAME_FULLY_QUALIFIED = \PHP_VERSION_ID >= 8_00_00 ? \T_NAME_FULLY_QUALIFIED : -804;
    public const T_NAME_QUALIFIED = \PHP_VERSION_ID >= 8_00_00 ? \T_NAME_QUALIFIED : -805;
    public const T_NAME_RELATIVE = \PHP_VERSION_ID >= 8_00_00 ? \T_NAME_RELATIVE : -806;

    // PHP 8.1+
    public const T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG = \PHP_VERSION_ID >= 8_01_00 ? \T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG : -811;
    public const T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG = \PHP_VERSION_ID >= 8_01_00 ? \T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG : -812;
    public const T_ENUM = \PHP_VERSION_ID >= 8_01_00 ? \T_ENUM : -813;
    public const T_READONLY = \PHP_VERSION_ID >= 8_01_00 ? \T_READONLY : -814;

    // PHP 8.4+
    public const T_PRIVATE_SET = \PHP_VERSION_ID >= 8_04_00 ? \T_PRIVATE_SET : -841;
    public const T_PROTECTED_SET = \PHP_VERSION_ID >= 8_04_00 ? \T_PROTECTED_SET : -842;
    public const T_PUBLIC_SET = \PHP_VERSION_ID >= 8_04_00 ? \T_PUBLIC_SET : -843;
    public const T_PROPERTY_C = \PHP_VERSION_ID >= 8_04_00 ? \T_PROPERTY_C : -844;

    // PHP 8.5+
    public const T_PIPE = \PHP_VERSION_ID >= 8_05_00 ? \T_PIPE : -851;
    public const T_VOID_CAST = \PHP_VERSION_ID >= 8_05_00 ? \T_VOID_CAST : -852;
}
