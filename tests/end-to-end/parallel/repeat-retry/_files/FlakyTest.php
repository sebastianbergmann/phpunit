<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelRepeatRetry;

use PHPUnit\Framework\Attributes\Retry;
use PHPUnit\Framework\TestCase;

final class FlakyTest extends TestCase
{
    private static int $attempts = 0;

    #[Retry(3)]
    public function testFlaky(): void
    {
        self::$attempts++;

        if (self::$attempts < 2) {
            $this->fail('Flaky failure on attempt ' . self::$attempts);
        }

        $this->assertTrue(true);
    }

    public function testStable(): void
    {
        $this->assertTrue(true);
    }
}
