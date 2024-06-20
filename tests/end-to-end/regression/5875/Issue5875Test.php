<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5875;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class Issue5875Test extends TestCase
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
    public function testThirdTestWhichUsesDataProvider($numberOfTestsBeforeThisOne): void
    {
        $this->assertSame($numberOfTestsBeforeThisOne, self::$destructsDone);
    }
}
