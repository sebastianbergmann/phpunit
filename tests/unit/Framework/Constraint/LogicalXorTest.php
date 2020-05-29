<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Framework\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\LogicalXor;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
final class LogicalXorTest extends TestCase
{
    public function providerFromConstraintsReturnsConstraint()
    {
        return [
            [0], [0, 1], [0, 1, 0],
        ];
    }

    /**
     * @dataProvider providerFromConstraintsReturnsConstraint
     */
    public function testFromConstraintsReturnsConstraint(int ...$args): void
    {
        $other       = 'Foo';
        $constraints = \array_map(function (bool $arg) use ($other) {
            return $this->getMockBuilder(Constraint::class)->getMock();
        }, $args);

        $constraint = LogicalXor::fromConstraints(...$constraints);

        $this->assertInstanceOf(LogicalXor::class, $constraint);
    }

    public function testSetConstraintsWithNonConstraintsObjectArrayIsTreatedAsIsEqual(): void
    {
        $constraint = new LogicalXor;

        $constraint->setConstraints(['cuckoo']);

        $this->assertSame('is equal to \'cuckoo\'', $constraint->toString());
    }

    public function providerEvaluateReturnsCorrectResult()
    {
        return [
            [],
            [0],       [1],
            [0, 0],    [0, 1],    [1, 0],    [1, 1],
            [0, 0, 0], [0, 0, 1], [0, 1, 0], [0, 1, 1],
            [1, 0, 0], [1, 0, 1], [1, 1, 0], [1, 1, 1],
        ];
    }

    /**
     * @dataProvider providerEvaluateReturnsCorrectResult
     */
    public function testEvaluateReturnsCorrectResult(int ...$args): void
    {
        $other       = 'Foo';
        $constraints = \array_map(function (bool $arg) use ($other) {
            $constraint = $this->getMockBuilder(Constraint::class)->getMock();

            $constraint
                ->expects($this->once())
                ->method('evaluate')
                ->with($this->identicalTo($other))
                ->willReturn($arg);
            $constraint
                ->expects($this->any())
                ->method('toString')
                ->with()
                ->willReturn($arg ? 'true' : 'false');

            return $constraint;
        }, $args);

        $initial  = (bool) \array_shift($args);
        $expected = \array_reduce($args, function (bool $carry, bool $item) {
            return $carry xor $item;
        }, $initial);

        $constraint = LogicalXor::fromConstraints(...$constraints);

        $message = 'Failed asserting that ' . $constraint->toString() . ' is ' . ($expected ? 'true' : 'false');
        $this->assertSame($expected, $constraint->evaluate($other, '', true), $message);
    }
}
