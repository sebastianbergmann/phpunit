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

final class SlowExampleTest extends TestCase
{
    #[Property(examples: 10)]
    public function testTakesLongerThanTheTimeLimitOfTheTestRun(int $value): void
    {
        usleep(1500000);

        $this->assertTrue(true);
    }

    public function testIsNotRun(): void
    {
        $this->assertTrue(true);
    }
}
