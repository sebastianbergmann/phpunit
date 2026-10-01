<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelStandardInput;

use const STDIN;
use function file_get_contents;
use function shell_exec;
use function stream_get_contents;
use PHPUnit\Framework\TestCase;

final class StandardInputTest extends TestCase
{
    public function testReadsItsStandardInput(): void
    {
        $this->assertSame('', stream_get_contents(STDIN));
    }

    public function testReadsPhpStdin(): void
    {
        $this->assertSame('', file_get_contents('php://stdin'));
    }

    public function testStartsAProcessThatReadsItsStandardInput(): void
    {
        $this->assertSame('', (string) shell_exec('cat'));
    }
}
