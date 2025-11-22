<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class RepeatInIsolationTest extends TestCase
{
    private Closure $notCloneableFixture;
    private int $counter;

    public function __clone()
    {
        if (isset($this->notCloneableFixture)) {
            throw new LogicException('Cannot clone RepeatInIsolationTest because it contains a non-cloneable fixture.');
        }
    }

    protected function setUp(): void
    {
        $this->counter             = 0;
        $this->notCloneableFixture = static fn () => true;
    }

    public function test1(): void
    {
        $this->assertTrue(($this->notCloneableFixture)());

        $this->counter++;
        $this->assertSame(1, $this->counter);
    }

    public function test2(): void
    {
        $this->assertTrue(($this->notCloneableFixture)());

        $this->counter++;
        $this->assertSame(1, $this->counter);
    }
}
