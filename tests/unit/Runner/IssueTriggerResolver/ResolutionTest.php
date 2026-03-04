<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\IssueTriggerResolver;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Resolution::class)]
#[Small]
#[Group('test-runner')]
final class ResolutionTest extends TestCase
{
    public function testHasCalleeReturnsTrueWhenCalleeIsSet(): void
    {
        $resolution = new Resolution('/path/to/callee.php', null);

        $this->assertTrue($resolution->hasCallee());
    }

    public function testHasCalleeReturnsFalseWhenCalleeIsNull(): void
    {
        $resolution = new Resolution(null, null);

        $this->assertFalse($resolution->hasCallee());
    }

    public function testCalleeReturnsSetValue(): void
    {
        $resolution = new Resolution('/path/to/callee.php', null);

        $this->assertSame('/path/to/callee.php', $resolution->callee());
    }

    public function testCalleeReturnsNullWhenNotSet(): void
    {
        $resolution = new Resolution(null, null);

        $this->assertNull($resolution->callee());
    }

    public function testHasCallerReturnsTrueWhenCallerIsSet(): void
    {
        $resolution = new Resolution(null, '/path/to/caller.php');

        $this->assertTrue($resolution->hasCaller());
    }

    public function testHasCallerReturnsFalseWhenCallerIsNull(): void
    {
        $resolution = new Resolution(null, null);

        $this->assertFalse($resolution->hasCaller());
    }

    public function testCallerReturnsSetValue(): void
    {
        $resolution = new Resolution(null, '/path/to/caller.php');

        $this->assertSame('/path/to/caller.php', $resolution->caller());
    }

    public function testCallerReturnsNullWhenNotSet(): void
    {
        $resolution = new Resolution(null, null);

        $this->assertNull($resolution->caller());
    }

    public function testBothCalleeAndCallerCanBeSet(): void
    {
        $resolution = new Resolution('/path/to/callee.php', '/path/to/caller.php');

        $this->assertTrue($resolution->hasCallee());
        $this->assertTrue($resolution->hasCaller());
        $this->assertSame('/path/to/callee.php', $resolution->callee());
        $this->assertSame('/path/to/caller.php', $resolution->caller());
    }
}
