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

final class FastExamplesTest extends TestCase
{
    #[Property(examples: 100000)]
    public function testHasMoreExamplesThanFitIntoTheTimeLimitOfTheTestRun(int $value): void
    {
        usleep(1000);

        $this->assertTrue(true);
    }

    public function testIsNotRun(): void
    {
        $this->assertTrue(true);
    }
}
