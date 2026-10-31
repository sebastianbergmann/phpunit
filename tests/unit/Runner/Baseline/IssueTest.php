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

use function realpath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\FileDoesNotExistException;

#[CoversClass(Issue::class)]
#[CoversClass(FileDoesNotExistException::class)]
#[CoversClass(FileDoesNotHaveLineException::class)]
#[Small]
final class IssueTest extends TestCase
{
    /**
     * @psalm-return array<string, array{0: non-empty-string, 1: non-empty-string}>
     */
    public static function providerDescriptionWithNameOfAnonymousClass(): array
    {
        return [
            'anonymous class declared in file with Unix path' => [
                'Undefined property: class@anonymous::$a',
                "Undefined property: class@anonymous\0/path/to/Source.php:76\$309::\$a",
            ],
            'anonymous class declared in file with Windows path' => [
                'Accessing static property class@anonymous::$a as non static',
                "Accessing static property class@anonymous\0D:\\path\\to\\Source.php:76\$1f::\$a as non static",
            ],
            'anonymous class that implements an interface' => [
                'Serializable@anonymous implements the Serializable interface, which is deprecated',
                "Serializable@anonymous\0/path/to/Source.php:62\$308 implements the Serializable interface, which is deprecated",
            ],
            'anonymous class declared in eval()\'d code' => [
                'Undefined property: class@anonymous::$a',
                "Undefined property: class@anonymous\0/path/to/Source.php(12) : eval()'d code:1\$0::\$a",
            ],
            'multiple anonymous classes' => [
                'class@anonymous and class@anonymous',
                "class@anonymous\0/path/to/Source.php:1\$0 and class@anonymous\0/path/to/Source.php:2\$1",
            ],
        ];
    }

    public function testHasFile(): void
    {
        $this->assertSame(
            realpath(__DIR__ . '/../../../_files/baseline/FileWithIssues.php'),
            $this->issue()->file(),
        );
    }

    public function testHasLine(): void
    {
        $this->assertSame(10, $this->issue()->line());
    }

    public function testHasHash(): void
    {
        $this->assertSame('6bf70f0b8c461415955e2d3a97cfbe664f5b957b', $this->issue()->hash());
    }

    public function testHasDescription(): void
    {
        $this->assertSame('Undefined variable $b', $this->issue()->description());
    }

    /**
     * @param non-empty-string $expected
     * @param non-empty-string $description
     */
    #[DataProvider('providerDescriptionWithNameOfAnonymousClass')]
    public function testRemovesFileLineAndCounterFromNameOfAnonymousClassInDescription(string $expected, string $description): void
    {
        $issue = Issue::from(
            realpath(__DIR__ . '/../../../_files/baseline/FileWithIssues.php'),
            10,
            null,
            $description,
        );

        $this->assertSame($expected, $issue->description());
    }

    public function testIsComparable(): void
    {
        $this->assertTrue($this->issue()->equals($this->issue()));
        $this->assertFalse($this->issue()->equals($this->anotherIssue()));
    }

    public function testCannotBeCreatedForFileThatDoesNotExist(): void
    {
        $this->expectException(FileDoesNotExistException::class);

        Issue::from(
            'does-not-exist.php',
            1,
            null,
            'description',
        );
    }

    public function testCannotBeCreatedForLineThatDoesNotExist(): void
    {
        $this->expectException(FileDoesNotHaveLineException::class);

        Issue::from(
            realpath(__DIR__ . '/../../../_files/baseline/FileWithIssues.php'),
            1234,
            null,
            'description',
        );
    }

    private function issue(): Issue
    {
        return Issue::from(
            realpath(__DIR__ . '/../../../_files/baseline/FileWithIssues.php'),
            10,
            null,
            'Undefined variable $b',
        );
    }

    private function anotherIssue(): Issue
    {
        return Issue::from(
            realpath(__DIR__ . '/../../../_files/baseline/FileWithIssues.php'),
            11,
            null,
            'Undefined variable $c',
        );
    }
}
