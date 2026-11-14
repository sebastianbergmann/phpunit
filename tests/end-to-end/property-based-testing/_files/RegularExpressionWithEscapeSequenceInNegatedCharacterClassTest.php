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

use function preg_split;
use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;

final class RegularExpressionWithEscapeSequenceInNegatedCharacterClassTest extends TestCase
{
    /**
     * @return ValueGenerator<string>
     */
    public static function separators(): ValueGenerator
    {
        return Generators::fromRegularExpression('/^[^\w]{100}$/u');
    }

    #[Property]
    public function testWordsAreSplitAtCharactersThatAreNotWordCharacters(#[ForAll('separators')] string $separators): void
    {
        $this->assertSame(['foo', 'bar'], preg_split('/\W+/u', 'foo' . $separators . 'bar'));
    }
}
