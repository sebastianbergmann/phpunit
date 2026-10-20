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

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class OtherFalsificationThatIsNotReproducedTest extends TestCase
{
    private static bool $falsifiedInAnotherWay = false;

    /**
     * @param int<0, 1000> $value
     */
    #[Property]
    public function testIsFalsifiedInAnotherWayOnlyOnce(int $value): void
    {
        if ($value >= 100 && $value < 500 && !self::$falsifiedInAnotherWay) {
            self::$falsifiedInAnotherWay = true;

            throw new RuntimeException('between 100 and 500');
        }

        $this->assertLessThan(500, $value);
    }
}
