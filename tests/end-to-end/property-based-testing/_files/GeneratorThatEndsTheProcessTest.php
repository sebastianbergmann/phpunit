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

final class GeneratorThatEndsTheProcessTest extends TestCase
{
    /**
     * @return ValueGenerator<int>
     */
    public static function integersWithExit(): ValueGenerator
    {
        return Generators::integers(0, 1000)->map(
            static function (int $value): int
            {
                if ($value >= 100) {
                    exit(0);
                }

                return $value;
            },
        );
    }

    #[Property]
    public function testExitsWhileArgumentsAreGenerated(#[ForAll('integersWithExit')] int $value): void
    {
        $this->assertLessThan(100, $value);
    }
}
