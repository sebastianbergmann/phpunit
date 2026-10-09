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
use PHPUnit\Framework\TestCase;

final class SlowShrinkingTest extends TestCase
{
    #[Property(examples: 10)]
    public function testTakesLongerToShrinkThanTheTimeLimitOfTheTestRun(int $value): void
    {
        usleep(100000);

        $this->assertLessThan(1000, $value);
    }
}
