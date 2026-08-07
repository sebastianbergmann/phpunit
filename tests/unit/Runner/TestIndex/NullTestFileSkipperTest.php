<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestIndex;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(NullTestFileSkipper::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-index')]
final class NullTestFileSkipperTest extends TestCase
{
    #[TestDox('Never skips loading a test file')]
    public function testNeverSkipsLoadingTestFile(): void
    {
        $this->assertFalse((new NullTestFileSkipper)->canSkipLoading(__FILE__, ['a-group']));
    }

    #[TestDox('Loads a test file without remembering anything about it')]
    public function testLoadsTestFileWithoutRememberingAnythingAboutIt(): void
    {
        $skipper = new NullTestFileSkipper;

        $this->assertSame('loaded', $skipper->record(__FILE__, static fn (): string => 'loaded'));

        $skipper->persist();

        $this->assertFalse($skipper->canSkipLoading(__FILE__, []));
    }

    #[TestDox('Does not swallow a test file that cannot be loaded')]
    public function testDoesNotSwallowTestFileThatCannotBeLoaded(): void
    {
        $skipper = new NullTestFileSkipper;

        $this->expectException(RuntimeException::class);

        $skipper->record(__FILE__, static function (): void
        {
            throw new RuntimeException('the file cannot be loaded');
        });
    }
}
