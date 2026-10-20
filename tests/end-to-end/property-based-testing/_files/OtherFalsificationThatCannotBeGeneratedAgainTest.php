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

use PHPUnit\Framework\Attributes\ForAll;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class OtherFalsificationThatCannotBeGeneratedAgainTest extends TestCase
{
    private static bool $falsifiedInAnotherWay = false;

    /**
     * @return ValueGenerator<int>
     */
    public static function integers(): ValueGenerator
    {
        return Generators::integers(0, 1000)->map(
            static function (int $value): int
            {
                if (self::$falsifiedInAnotherWay && $value >= 100 && $value < 500) {
                    throw new RuntimeException('cannot be generated again');
                }

                return $value;
            },
        );
    }

    #[Property]
    public function testIsFalsifiedInAnotherWayByAValueThatCannotBeGeneratedAgain(#[ForAll('integers')] int $value): void
    {
        if ($value >= 100 && $value < 500) {
            self::$falsifiedInAnotherWay = true;

            throw new RuntimeException('between 100 and 500');
        }

        $this->assertLessThan(500, $value);
    }
}
