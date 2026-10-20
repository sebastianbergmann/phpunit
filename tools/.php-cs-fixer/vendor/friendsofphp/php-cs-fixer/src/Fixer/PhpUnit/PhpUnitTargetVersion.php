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

namespace PhpCsFixer\Fixer\PhpUnit;

use Composer\Semver\Comparator;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PhpUnitTargetVersion
{
    public const VERSION_3_0 = '3.0';
    public const VERSION_3_2 = '3.2';
    public const VERSION_3_5 = '3.5';
    public const VERSION_4_3 = '4.3';
    public const VERSION_4_8 = '4.8';
    public const VERSION_5_0 = '5.0';
    public const VERSION_5_2 = '5.2';
    public const VERSION_5_4 = '5.4';
    public const VERSION_5_5 = '5.5';
    public const VERSION_5_6 = '5.6';
    public const VERSION_5_7 = '5.7';
    public const VERSION_6_0 = '6.0';
    public const VERSION_7_5 = '7.5';
    public const VERSION_8_4 = '8.4';
    public const VERSION_9_1 = '9.1';
    public const VERSION_10_0 = '10.0';
    public const VERSION_11_0 = '11.0';
    public const VERSION_NEWEST = 'newest';

    private function __construct() {}

    public static function fulfills(string $candidate, string $target): bool
    {
        if (self::VERSION_NEWEST === $target) {
            throw new \LogicException(\sprintf('Parameter `target` shall not be provided as "%s", determine proper target for tested PHPUnit feature instead.', self::VERSION_NEWEST));
        }

        if (self::VERSION_NEWEST === $candidate) {
            return true;
        }

        return Comparator::greaterThanOrEqualTo($candidate, $target);
    }
}
