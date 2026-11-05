<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;

final class BitStrings
{
    /**
     * @return ValueGenerator<non-empty-string>
     */
    public static function withoutLeadingZeros(): ValueGenerator
    {
        return Generators::strings(alphabet: '01', maximumLength: 62)->map(
            static function (string $bits): string
            {
                return '1' . $bits;
            },
        );
    }
}
