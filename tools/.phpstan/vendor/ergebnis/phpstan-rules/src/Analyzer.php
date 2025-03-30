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

use PhpParser\Node;

/**
 * @internal
 */
final class Analyzer
{
    public function hasNullDefaultValue(Node\Param $parameter): bool
    {
        if (!$parameter->default instanceof Node\Expr\ConstFetch) {
            return false;
        }

        return 'null' === $parameter->default->name->toLowerString();
    }

    /**
     * @param null|Node\ComplexType|Node\Identifier|Node\Name $typeDeclaration
     */
    public function isNullableTypeDeclaration($typeDeclaration): bool
    {
        if ($typeDeclaration instanceof Node\NullableType) {
            return true;
        }

        if ($typeDeclaration instanceof Node\UnionType) {
            foreach ($typeDeclaration->types as $type) {
                if (
                    $type instanceof Node\Identifier
                    && 'null' === $type->toLowerString()
                ) {
                    return true;
                }

                if (
                    $type instanceof Node\Name\FullyQualified
                    && $type->hasAttribute('originalName')
                ) {
                    $originalName = $type->getAttribute('originalName');

                    if (
                        $originalName instanceof Node\Name
                        && 'null' === $originalName->toLowerString()
                    ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
