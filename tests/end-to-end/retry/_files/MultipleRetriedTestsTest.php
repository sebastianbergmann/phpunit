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

final class MultipleRetriedTestsTest extends TestCase
{
    private static int $countOne = 0;
    private static int $countTwo = 0;

    #[Retry(3)]
    public function testOne(): void
    {
        self::$countOne++;

        if (self::$countOne < 3) {
            $this->fail('Failure before third attempt');
        }

        $this->assertTrue(true);
    }

    #[Retry(2)]
    public function testTwo(): void
    {
        self::$countTwo++;

        if (self::$countTwo < 2) {
            $this->fail('Failure on first attempt');
        }

        $this->assertTrue(true);
    }
}
