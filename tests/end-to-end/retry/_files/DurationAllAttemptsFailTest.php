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

use function sprintf;
use function usleep;
use PHPUnit\Framework\Attributes\Retry;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DurationAllAttemptsFailTest extends TestCase
{
    private static int $failCount  = 0;
    private static int $errorCount = 0;

    #[Retry(3)]
    public function testThatFails(): void
    {
        self::$failCount++;

        usleep(150000);

        $this->fail(sprintf('Failure on attempt %d', self::$failCount));
    }

    #[Retry(3)]
    public function testThatErrors(): void
    {
        self::$errorCount++;

        usleep(150000);

        throw new RuntimeException(sprintf('Error on attempt %d', self::$errorCount));
    }
}
