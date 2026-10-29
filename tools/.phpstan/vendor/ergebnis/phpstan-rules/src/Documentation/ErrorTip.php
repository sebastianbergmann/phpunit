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

use Composer\InstalledVersions;

/**
 * @internal
 */
final class ErrorTip
{
    private const NAMESPACE_PREFIX = 'Ergebnis\\PHPStan\\Rules\\';
    private const PACKAGE_NAME = 'ergebnis/phpstan-rules';

    /**
     * Returns the tip of the rule without Markdown backticks, followed by a link to the documentation of the rule for the installed version.
     */
    public static function for(HasRuleDefinition $rule): string
    {
        return \sprintf(
            '%s Learn more at https://github.com/ergebnis/phpstan-rules/blob/%s/doc/rules/%s.md',
            \str_replace(
                '`',
                '',
                $rule->ruleDefinition()->tip(),
            ),
            self::installedReference(),
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

    /**
     * Returns the tag of the installed release, or the commit of an installed development version, or main when neither is known.
     */
    private static function installedReference(): string
    {
        if (!InstalledVersions::isInstalled(self::PACKAGE_NAME)) {
            return 'main';
        }

        $version = InstalledVersions::getPrettyVersion(self::PACKAGE_NAME);

        if (
            \is_string($version)
            && 1 === \preg_match('/^\d+\.\d+\.\d+$/', $version)
        ) {
            return $version;
        }

        $reference = InstalledVersions::getReference(self::PACKAGE_NAME);

        if (
            \is_string($reference)
            && '' !== $reference
        ) {
            return $reference;
        }

        return 'main';
    }
}
