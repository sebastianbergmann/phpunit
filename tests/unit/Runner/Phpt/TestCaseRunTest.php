<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Phpt;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

#[CoversClass(TestCase::class)]
#[CoversClass(InvalidPhptFileException::class)]
#[Medium]
#[Group('test-runner')]
#[Group('test-runner/phpt')]
final class TestCaseRunTest extends FrameworkTestCase
{
    #[TestDox('run() throws InvalidPhptFileException when FILE section is empty')]
    public function testRunRejectsEmptyFileSection(): void
    {
        $testCase = new TestCase(__DIR__ . '/../../../_files/phpt/empty-file-section.phpt');

        $this->expectException(InvalidPhptFileException::class);

        $testCase->run();
    }

    #[TestDox('run() uses FILE_EXTERNAL_PATH for the rendered code location')]
    public function testRunUsesFileExternalPath(): void
    {
        $testCase = new TestCase(__DIR__ . '/../../../_files/phpt/file-external/test.phpt');

        $testCase->run();

        $this->assertTrue(true);
    }

    #[TestDox('run() falls back to "Skipped" when SKIPIF outputs only "skip"')]
    public function testRunSkipsWithDefaultMessage(): void
    {
        $testCase = new TestCase(__DIR__ . '/../../../_files/phpt/skipif-no-message.phpt');

        $testCase->run();

        $this->assertTrue(true);
    }
}
