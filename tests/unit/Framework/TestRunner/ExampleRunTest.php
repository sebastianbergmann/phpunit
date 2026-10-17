<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestRunner;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExampleRun::class)]
#[Small]
final class ExampleRunTest extends TestCase
{
    public function testHasNumberOfAssertions(): void
    {
        $this->assertSame(2, new ExampleRun(2, false, null)->numberOfAssertions());
    }

    public function testMayBeExpectedToPerformNoAssertions(): void
    {
        $this->assertTrue(new ExampleRun(0, true, null)->doesNotPerformAssertions());
        $this->assertFalse(new ExampleRun(0, false, null)->doesNotPerformAssertions());
    }

    public function testMayHaveContributedToCodeCoverage(): void
    {
        $this->assertTrue(new ExampleRun(1, false, true)->contributedToCodeCoverage());
        $this->assertFalse(new ExampleRun(1, false, false)->contributedToCodeCoverage());
        $this->assertNull(new ExampleRun(1, false, null)->contributedToCodeCoverage());
    }
}
