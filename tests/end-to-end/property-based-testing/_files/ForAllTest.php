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

use function bindec;
use function decbin;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/BitStrings.php';

final class ForAllTest extends TestCase
{
    /**
     * @return ValueGenerator<non-empty-string>
     */
    public static function bitStringsWithoutLeadingZeros(): ValueGenerator
    {
        return Generators::strings(alphabet: '01', maximumLength: 62)->map(
            static function (string $bits): string
            {
                return '1' . $bits;
            },
        );
    }

    #[Property]
    public function testGeneratorIsSpecifiedWithAMethod(#[ForAll('bitStringsWithoutLeadingZeros')] string $bits): void
    {
        $this->assertSame($bits, decbin(bindec($bits)));
    }

    #[Property]
    public function testGeneratorIsSpecifiedWithAMethodOfAnotherClass(#[ForAll([BitStrings::class, 'withoutLeadingZeros'])] string $bits): void
    {
        $this->assertSame($bits, decbin(bindec($bits)));
    }
}
