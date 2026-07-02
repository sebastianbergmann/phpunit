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

use PHPUnit\Framework\TestCase;

final class RepeatFailureTest extends TestCase
{
    private static int $runs = 0;

    public function testFailsOnSecondRepetition(): void
    {
        self::$runs++;

        if (self::$runs === 2) {
            $this->fail('Failure on repetition ' . self::$runs);
        }

        $this->assertTrue(true);
    }
}
