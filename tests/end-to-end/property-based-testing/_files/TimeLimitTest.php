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

use function usleep;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
final class TimeLimitTest extends TestCase
{
    /**
     * @param int<0, 100> $value
     */
    #[Property(examples: 50)]
    public function testDoesNotTerminateForValuesGreaterThanFifty(int $value): void
    {
        while ($value > 50) {
            usleep(1000);
        }

        $this->assertLessThanOrEqual(50, $value);
    }
}
