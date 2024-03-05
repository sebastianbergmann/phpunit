<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use ArrayIterator;
use ArrayObject;
use EmptyIterator;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\GeneratorNotSupportedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ExceptionThrowingIteratorAggregate;
use PHPUnit\TestFixture\TestIterator;
use PHPUnit\TestFixture\TestIterator2;
use PHPUnit\TestFixture\TestIteratorAggregate;
use PHPUnit\TestFixture\TestIteratorAggregate2;

#[CoversClass(Count::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class CountTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                true,
                '',
                0,
                [],
            ],

            [
                true,
                '',
                1,
                ['value'],
            ],

            [
                true,
                '',
                0,
                new EmptyIterator,
            ],

            [
                true,
                '',
                5,
                new ArrayObject([1, 2, 3, 4, 5]),
            ],

            [
                true,
                '',
                1,
                new ArrayIterator(['value']),
            ],

            [
                true,
                '',
                2,
                new TestIterator(['value', 'value']),
            ],

            [
                true,
                '',
                2,
                new TestIteratorAggregate(new TestIterator(['value', 'value'])),
            ],

            [
                true,
                '',
                2,
                new TestIteratorAggregate2(new TestIteratorAggregate(new TestIterator(['value', 'value']))),
            ],

            [
                false,
                'Failed asserting that actual size 0 matches expected size 1.',
                1,
                1,
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testCanBeEvaluated(bool $result, string $failureDescription, int $expected, mixed $actual): void
    {
        $constraint = new Count($expected);

        $this->assertSame($result, $constraint->evaluate($actual, returnResult: true));

        if ($result) {
            return;
        }

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($failureDescription);

        $constraint->evaluate($actual);
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame('count matches 1', (new Count(1))->toString());
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new Count(1)));
    }

    public function testCountDoesNotChangeIteratorKey(): void
    {
        $countConstraint = new Count(2);

        // test with 1st implementation of Iterator
        $it = new TestIterator([1, 2]);

        $countConstraint->evaluate($it, returnResult: true);
        $this->assertSame(1, $it->current());

        $it->next();
        $countConstraint->evaluate($it, returnResult: true);
        $this->assertSame(2, $it->current());

        $it->next();
        $countConstraint->evaluate($it, returnResult: true);
        $this->assertFalse($it->valid());

        // test with 2nd implementation of Iterator
        $it = new TestIterator2([1, 2]);

        $countConstraint = new Count(2);
        $countConstraint->evaluate($it, returnResult: true);
        $this->assertSame(1, $it->current());

        $it->next();
        $countConstraint->evaluate($it, returnResult: true);
        $this->assertSame(2, $it->current());

        $it->next();
        $countConstraint->evaluate($it, returnResult: true);
        $this->assertFalse($it->valid());

        // test with IteratorAggregate
        $it = new TestIterator([1, 2]);
        $ia = new TestIteratorAggregate($it);

        $countConstraint = new Count(2);
        $countConstraint->evaluate($ia, returnResult: true);
        $this->assertSame(1, $it->current());

        $it->next();
        $countConstraint->evaluate($ia, returnResult: true);
        $this->assertSame(2, $it->current());

        $it->next();
        $countConstraint->evaluate($ia, returnResult: true);
        $this->assertFalse($it->valid());

        // test with nested IteratorAggregate
        $it  = new TestIterator([1, 2]);
        $ia  = new TestIteratorAggregate($it);
        $ia2 = new TestIteratorAggregate2($ia);

        $countConstraint = new Count(2);
        $countConstraint->evaluate($ia2, returnResult: true);
        $this->assertSame(1, $it->current());

        $it->next();
        $countConstraint->evaluate($ia2, returnResult: true);
        $this->assertSame(2, $it->current());

        $it->next();
        $countConstraint->evaluate($ia2, returnResult: true);
        $this->assertFalse($it->valid());
    }

    public function testDoesNotAcceptGenerators(): void
    {
        $constraint = new Count(0);

        $this->expectException(GeneratorNotSupportedException::class);

        $constraint->evaluate($this->generator());
    }

    public function testWrapsExceptionFromIteratorAggregate(): void
    {
        $constraint = new Count(0);

        $this->expectException(Exception::class);

        $constraint->evaluate(new ExceptionThrowingIteratorAggregate);
    }

    private function generator(): Generator
    {
        yield 1;
    }
}
