<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Baseline;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\FileDoesNotExistException;

#[CoversClass(Issue::class)]
#[CoversClass(FileDoesNotExistException::class)]
#[CoversClass(FileDoesNotHaveLineException::class)]
#[Small]
final class IssueTest extends TestCase
{
    public function testHasFile(): void
    {
        $this->assertSame('file.php', $this->issue()->file());
    }

    public function testHasLine(): void
    {
        $this->assertSame(1, $this->issue()->line());
    }

    public function testHasHash(): void
    {
        $this->assertSame('hash', $this->issue()->hash());
    }

    public function testHasDescription(): void
    {
        $this->assertSame('description', $this->issue()->description());
    }

    public function testIsComparable(): void
    {
        $this->assertTrue($this->issue()->equals($this->issue()));
        $this->assertFalse($this->issue()->equals($this->anotherIssue()));
    }

    private function issue(): Issue
    {
        return Issue::from(
            'file.php',
            1,
            'hash',
            'description',
        );
    }

    private function anotherIssue(): Issue
    {
        return Issue::from(
            'file.php',
            2,
            'hash',
            'description',
        );
    }
}
