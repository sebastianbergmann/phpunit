<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(VersionComparisonOperator::class)]
#[CoversClass(InvalidVersionOperatorException::class)]
#[Small]
final class VersionComparisonOperatorTest extends TestCase
{
    /**
     * @return non-empty-list<non-empty-list<string>>
     */
    public static function validValues(): array
    {
        return [
            ['<'],
            ['lt'],
            ['<='],
            ['le'],
            ['>'],
            ['gt'],
            ['>='],
            ['ge'],
            ['=='],
            ['='],
            ['eq'],
            ['!='],
            ['<>'],
            ['ne'],
        ];
    }

    #[DataProvider('validValues')]
    #[TestDox('Can be created from "$string"')]
    public function testCanBeCreatedFromValidString(string $string): void
    {
        $this->assertSame($string, (new VersionComparisonOperator($string))->asString());
    }

    public function testCannotBeCreatedFromInvalidString(): void
    {
        $this->expectException(InvalidVersionOperatorException::class);

        new VersionComparisonOperator('');
    }
}
