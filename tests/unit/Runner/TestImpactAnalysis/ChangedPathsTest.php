<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use const DIRECTORY_SEPARATOR;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChangedPaths::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class ChangedPathsTest extends TestCase
{
    public function testReadsOnePathPerLine(): void
    {
        $this->assertSame(
            [
                'src/Money.php',
                'src/Currency.php',
                'src/Service',
            ],
            ChangedPaths::readFrom(self::file('changed-paths.txt')),
        );
    }

    public function testReadsNoPathFromAFileThatHasNothingInIt(): void
    {
        $this->assertSame([], ChangedPaths::readFrom(self::file('no-changed-paths.txt')));
    }

    public function testReadsFromStandardInputWhenTheFileIsADash(): void
    {
        $this->assertSame(
            [
                'src/Money.php',
                'src/Currency.php',
                'src/Service',
            ],
            ChangedPaths::readFrom('-', self::file('changed-paths.txt')),
        );
    }

    public function testDoesNotReadFromAFileThatIsNotThere(): void
    {
        $this->assertNull(ChangedPaths::readFrom(self::file('does-not-exist.txt')));
    }

    /**
     * @param non-empty-string $name
     *
     * @return non-empty-string
     */
    private static function file(string $name): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . $name;
    }
}
