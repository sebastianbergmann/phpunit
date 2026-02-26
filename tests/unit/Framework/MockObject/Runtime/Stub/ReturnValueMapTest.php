<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Stub;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;

#[CoversClass(ReturnValueMap::class)]
#[Group('test-doubles')]
#[Small]
final class ReturnValueMapTest extends TestCase
{
    public function testReturnsNullWhenNoEntryMatchesArguments(): void
    {
        $stub   = $this->createStub(AnInterface::class);
        $map    = new ReturnValueMap([[5, 'result']]);
        $result = $map->invoke(new Invocation(AnInterface::class, 'doSomething', [99], 'bool', $stub));

        $this->assertNull($result);
    }

    public function testSkipsNonArrayEntry(): void
    {
        $stub   = $this->createStub(AnInterface::class);
        $map    = new ReturnValueMap(['not-an-array', [1, 'x']]);
        $result = $map->invoke(new Invocation(AnInterface::class, 'doSomething', [1], 'bool', $stub));

        $this->assertSame('x', $result);
    }

    public function testSkipsEntryWithWrongParameterCount(): void
    {
        $stub   = $this->createStub(AnInterface::class);
        $map    = new ReturnValueMap([[1, 2, 'wrong'], [3, 'correct']]);
        $result = $map->invoke(new Invocation(AnInterface::class, 'doSomething', [3], 'bool', $stub));

        $this->assertSame('correct', $result);
    }
}
