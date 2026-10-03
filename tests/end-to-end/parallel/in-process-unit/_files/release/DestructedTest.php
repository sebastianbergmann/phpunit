<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelInProcessUnitRelease;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DoNotRunInParallel;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

/**
 * Runs in the main process. Each test asserts that the objects of the tests
 * that ran before it have been destructed.
 */
#[DoNotRunInParallel]
final class DestructedTest extends TestCase
{
    private static int $destructsDone = 0;

    public function __destruct()
    {
        self::$destructsDone++;
    }

    public function testFirstTest(): void
    {
        $this->assertSame(0, self::$destructsDone);
    }

    #[Depends('testFirstTest')]
    public function testSecondTest(): void
    {
        $this->assertSame(1, self::$destructsDone);
    }

    #[Depends('testSecondTest')]
    #[TestWith([2])]
    #[TestWith([3])]
    #[TestWith([4])]
    public function testThirdTestWhichUsesDataProvider(int $numberOfTestsBeforeThisOne): void
    {
        $this->assertSame($numberOfTestsBeforeThisOne, self::$destructsDone);
    }
}
