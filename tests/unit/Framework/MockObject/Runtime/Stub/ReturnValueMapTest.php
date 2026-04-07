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
use PHPUnit\Framework\Constraint\GreaterThan;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\ExpectationFailedException;
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

    public function testMatchesUsingConstraint(): void
    {
        $stub   = $this->createStub(AnInterface::class);
        $map    = new ReturnValueMap([[new IsIdentical(42), 'matched']]);
        $result = $map->invoke(new Invocation(AnInterface::class, 'doSomething', [42], 'bool', $stub));

        $this->assertSame('matched', $result);
    }

    public function testConstraintThatDoesNotMatchIsSkipped(): void
    {
        $stub   = $this->createStub(AnInterface::class);
        $map    = new ReturnValueMap([[new IsIdentical(42), 'matched']]);
        $result = $map->invoke(new Invocation(AnInterface::class, 'doSomething', [99], 'bool', $stub));

        $this->assertNull($result);
    }

    public function testMixedConstraintAndLiteralMatch(): void
    {
        $stub = $this->createStub(AnInterface::class);
        $map  = new ReturnValueMap([['literal', new GreaterThan(5), 'result']]);

        $matched = $map->invoke(new Invocation(AnInterface::class, 'doSomething', ['literal', 10], 'bool', $stub));

        $this->assertSame('result', $matched);

        $notMatched = $map->invoke(new Invocation(AnInterface::class, 'doSomething', ['literal', 3], 'bool', $stub));

        $this->assertNull($notMatched);
    }

    public function testStrictModeThrowsWhenNoEntryMatches(): void
    {
        $stub = $this->createStub(AnInterface::class);
        $map  = new ReturnValueMap([[5, 'result']], true);

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessageIsOrContains('No entry in the value map matched the invocation of');

        $map->invoke(new Invocation(AnInterface::class, 'doSomething', [99], 'bool', $stub));
    }

    public function testStrictModeReturnsValueWhenEntryMatches(): void
    {
        $stub   = $this->createStub(AnInterface::class);
        $map    = new ReturnValueMap([[5, 'result']], true);
        $result = $map->invoke(new Invocation(AnInterface::class, 'doSomething', [5], 'bool', $stub));

        $this->assertSame('result', $result);
    }

    public function testStrictModeWithConstraint(): void
    {
        $stub = $this->createStub(AnInterface::class);
        $map  = new ReturnValueMap([[new GreaterThan(5), 'result']], true);

        $this->assertSame(
            'result',
            $map->invoke(new Invocation(AnInterface::class, 'doSomething', [10], 'bool', $stub)),
        );

        $this->expectException(ExpectationFailedException::class);

        $map->invoke(new Invocation(AnInterface::class, 'doSomething', [3], 'bool', $stub));
    }
}
