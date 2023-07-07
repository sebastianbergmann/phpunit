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
use PHPUnit\Runner\FileDoesNotHaveLineException;

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

    public function testCanBeCreatedFromFileAndLine(): void
    {
        $file        = __DIR__ . '/../../../_files/FileWithIssue.php';
        $line        = 1;
        $hash        = 'da6257d768d43cd968a1d3ceae8d241ae9d2ef36';
        $description = 'description';
        $issue       = Issue::fromFileAndLine($file, $line, $description);

        $this->assertSame($file, $issue->file());
        $this->assertSame($line, $issue->line());
        $this->assertSame($hash, $issue->hash());
        $this->assertSame($description, $issue->description());
    }

    public function testCannotBeCreatedFromFileThatDoesNotExist(): void
    {
        $this->expectException(FileDoesNotExistException::class);
        $this->expectExceptionMessage('File "DoesNotExist.php" does not exist');

        Issue::fromFileAndLine('DoesNotExist.php', 1, 'description');
    }

    public function testCannotBeCreatedFromLineThatDoesNotExist(): void
    {
        $file = __DIR__ . '/../../../_files/FileWithIssue.php';

        $this->expectException(FileDoesNotHaveLineException::class);
        $this->expectExceptionMessage('File "' . $file . '" does not have line 100');

        Issue::fromFileAndLine($file, 100, 'description');
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
