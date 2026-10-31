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

final class GeneratorThatThrowsAfterPropertyWasFalsifiedTest extends TestCase
{
    private static bool $falsified = false;

    /**
     * @return ValueGenerator<int>
     */
    public static function integers(): ValueGenerator
    {
        return Generators::integers(1, 100)->map(
            static function (int $value): int
            {
                if (self::$falsified) {
                    throw new RuntimeException('The property was falsified');
                }

                return $value;
            },
        );
    }

    #[Property(examples: 10)]
    public function testIsFalsifiedByTheFirstExample(#[ForAll('integers')] int $value): void
    {
        self::$falsified = true;

        $this->assertLessThan(1, $value);
    }
}
