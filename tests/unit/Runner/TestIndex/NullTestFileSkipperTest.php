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

    #[TestDox('Does nothing when a test file is recorded or the index is written')]
    public function testDoesNothing(): void
    {
        $skipper = new NullTestFileSkipper;

        $skipper->startRecording(__FILE__);
        $skipper->stopRecording();
        $skipper->persist();

        $this->assertFalse($skipper->canSkipLoading(__FILE__, []));
    }
}
