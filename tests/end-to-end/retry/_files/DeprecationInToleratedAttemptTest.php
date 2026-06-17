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

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\Attributes\Retry;
use PHPUnit\Framework\TestCase;

final class DeprecationInToleratedAttemptTest extends TestCase
{
    private static int $count = 0;

    #[Retry(2)]
    public function testOne(): void
    {
        self::$count++;

        if (self::$count < 2) {
            trigger_error('Deprecation in tolerated attempt', E_USER_DEPRECATED);

            $this->fail('Failure on first attempt');
        }

        $this->assertTrue(true);
    }
}
