<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/phpstan-rules
 */

namespace Ergebnis\PHPStan\Rules\Documentation;

/**
 * @internal
 */
final class ErrorTip
{
    private const NAMESPACE_PREFIX = 'Ergebnis\\PHPStan\\Rules\\';

    public static function for(HasRuleDefinition $rule): string
    {
        return self::forReference(
            $rule,
            Reference::installed(),
        );
    }

    public static function forReference(
        HasRuleDefinition $rule,
        Reference $reference
    ): string {
        return \sprintf(
            '%s Learn more at https://github.com/ergebnis/phpstan-rules/blob/%s/doc/rules/%s.md',
            $rule->ruleDefinition()->tip()->toPlainText(),
            $reference->toString(),
            \str_replace(
                '\\',
                '/',
                \mb_substr(
                    \get_class($rule),
                    \mb_strlen(self::NAMESPACE_PREFIX),
                ),
            ),
        );
    }
}
