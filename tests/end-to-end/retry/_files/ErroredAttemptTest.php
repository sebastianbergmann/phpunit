<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Retry;

use PHPUnit\Framework\Attributes\Retry;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ErroredAttemptTest extends TestCase
{
    private static int $count = 0;

    #[Retry(2)]
    public function testOne(): void
    {
        self::$count++;

        if (self::$count < 2) {
            throw new RuntimeException('Error on first attempt');
        }

        $this->assertTrue(true);
    }
}
