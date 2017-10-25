<?php
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
use PHPUnit\Framework\Constraint\LogicalAnd;
use PHPUnit\Framework\TestCase;

final class LogicalAndTest extends TestCase
{
    public function testFromConstraintsReturnsConstraint()
    {
        $other = 'Foo';
        $count = 5;

        $constraints = \array_map(function () use ($other) {
            $constraint = $this->getMockBuilder(Constraint::class)->getMock();

            $constraint
                ->expects($this->once())
                ->method('evaluate')
                ->with($this->identicalTo($other))
                ->willReturn(true);

            return $constraint;
        }, \array_fill(0, $count, null));

        $constraint = LogicalAnd::fromConstraints(...$constraints);

        $this->assertInstanceOf(LogicalAnd::class, $constraint);
        $this->assertTrue($constraint->evaluate($other, '', true));
    }
}
