<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;

class CountTest extends ConstraintTestCase
{
    public function testCount(): void
    {
        $countConstraint = new Count(3);
        $this->assertTrue($countConstraint->evaluate([1, 2, 3], '', true));

        $countConstraint = new Count(0);
        $this->assertTrue($countConstraint->evaluate([], '', true));

        $countConstraint = new Count(2);
        $it              = new \TestIterator([1, 2]);
        $ia              = new \TestIteratorAggregate($it);
        $ia2             = new \TestIteratorAggregate2($ia);

        $this->assertTrue($countConstraint->evaluate($it, '', true));
        $this->assertTrue($countConstraint->evaluate($ia, '', true));
        $this->assertTrue($countConstraint->evaluate($ia2, '', true));
    }

    public function testCountDoesNotChangeIteratorKey(): void
    {
        $countConstraint = new Count(2);

        // test with 1st implementation of Iterator
        $it = new \TestIterator([1, 2]);

        $countConstraint->evaluate($it, '', true);
        $this->assertEquals(1, $it->current());

        $it->next();
        $countConstraint->evaluate($it, '', true);
        $this->assertEquals(2, $it->current());

        $it->next();
        $countConstraint->evaluate($it, '', true);
        $this->assertFalse($it->valid());

        // test with 2nd implementation of Iterator
        $it = new \TestIterator2([1, 2]);

        $countConstraint = new Count(2);
        $countConstraint->evaluate($it, '', true);
        $this->assertEquals(1, $it->current());

        $it->next();
        $countConstraint->evaluate($it, '', true);
        $this->assertEquals(2, $it->current());

        $it->next();
        $countConstraint->evaluate($it, '', true);
        $this->assertFalse($it->valid());

        // test with IteratorAggregate
        $it = new \TestIterator([1, 2]);
        $ia = new \TestIteratorAggregate($it);

        $countConstraint = new Count(2);
        $countConstraint->evaluate($ia, '', true);
        $this->assertEquals(1, $it->current());

        $it->next();
        $countConstraint->evaluate($ia, '', true);
        $this->assertEquals(2, $it->current());

        $it->next();
        $countConstraint->evaluate($ia, '', true);
        $this->assertFalse($it->valid());

        // test with nested IteratorAggregate
        $it  = new \TestIterator([1, 2]);
        $ia  = new \TestIteratorAggregate($it);
        $ia2 = new \TestIteratorAggregate2($ia);

        $countConstraint = new Count(2);
        $countConstraint->evaluate($ia2, '', true);
        $this->assertEquals(1, $it->current());

        $it->next();
        $countConstraint->evaluate($ia2, '', true);
        $this->assertEquals(2, $it->current());

        $it->next();
        $countConstraint->evaluate($ia2, '', true);
        $this->assertFalse($it->valid());
    }

    public function testDoesNotRewindGeneratorAtStartingPosition(): void
    {
        $countConstraint = new Count(3);
        $generator       = (new \TestGeneratorMaker())->create([1, 2, 3]);

        $this->assertCountSucceeds($countConstraint, $generator);
        $this->assertNull($generator->current());
    }

    public function testOnlyCountRemainingGeneratorElements(): void
    {
        $countConstraint = new Count(2);
        $generator       = (new \TestGeneratorMaker())->create([1, 2, 3]);
        $generator->next();

        $this->assertCountSucceeds($countConstraint, $generator);
        $this->assertNull($generator->current());
    }

    public function testCountsExhaustedGeneratorsAsZero(): void
    {
        $countConstraint = new Count(0);
        $generator       = (new \TestGeneratorMaker())->create([1]);
        $generator->next();

        $this->assertCountSucceeds($countConstraint, $generator);
        $this->assertNull($generator->current());
    }

    public function testFullyCountsIteratorsNotAtTheirStartingPosition(): void
    {
        $iterator = new \TestIterator([1, 2, 3]);
        $iterator->next();

        $this->assertCountSucceeds(
            new Count(3),
            $iterator
        );
    }

    public function testCountTraversable(): void
    {
        $countConstraint = new Count(5);

        // DatePeriod is used as an object that is Traversable but does not
        // implement Iterator or IteratorAggregate. The following ISO 8601
        // recurring time interval will yield five total DateTime objects.
        $datePeriod = new \DatePeriod('R4/2017-05-01T00:00:00Z/P1D');

        $this->assertInstanceOf(\Traversable::class, $datePeriod);
        $this->assertNotInstanceOf(\Iterator::class, $datePeriod);
        $this->assertNotInstanceOf(\IteratorAggregate::class, $datePeriod);
        $this->assertTrue($countConstraint->evaluate($datePeriod, '', true));
    }

    public function testCountingNonRewindableIteratorWithDifferentCount(): void
    {
        $this->assertCountFails(
            new Count(2),
            new \NoRewindIterator(new \ArrayIterator([1, 2, 3]))
        );
    }

    public function testCountingNonRewindableIteratorWithSameCount(): void
    {
        $this->assertCountSucceeds(
            new Count(2),
            new \NoRewindIterator(new \ArrayIterator([1, 2]))
        );
    }

    public function assertCountFails(Count $count, iterable $iterable): void
    {
        $this->assertFalse($count->evaluate($iterable, '', true));
    }

    public function assertCountSucceeds(Count $count, iterable $iterable): void
    {
        $this->assertTrue($count->evaluate($iterable, '', true));
    }

    public function testCountingDifferentGeneratorsSequentiallyWorks(): void
    {
        $count = new Count(2);

        $generatorMaker             = new \TestGeneratorMaker();
        $generatorWithThreeElements = $generatorMaker->create(['a', 'b', 'c']);
        $generatorWithTwoElements   = $generatorMaker->create(['a', 'b']);

        $this->assertCountFails($count, $generatorWithThreeElements);
        $this->assertCountSucceeds($count, $generatorWithTwoElements);
    }

    public function testFailureMessageIsCorrectForGenerators(): void
    {
        $this->assertEvaluateFailsWithMessage(
            new Count(1),
            (new \TestGeneratorMaker())->create(['a', 'b']),
            'actual size 2 matches expected size 1'
        );
    }

    public function testFailureMessageIsCorrectForNonRewindableIterators(): void
    {
        $this->assertEvaluateFailsWithMessage(
            new Count(1),
            new \NoRewindIterator(new \ArrayIterator([1, 2, 3])),
            'actual size 3 matches expected size 1'
        );
    }

    public function testFailureMessageIsCorrectForTraversablesThatAreNotIteratorsOrIteratorAggregates(): void
    {
        $this->assertEvaluateFailsWithMessage(
            new Count(1),
            $this->newPdoStatementWithThreeValues(),
            'actual size 3 matches expected size 1'
        );
    }

    private function assertEvaluateFailsWithMessage(Count $count, iterable $iterable, string $message): void
    {
        try {
            $count->evaluate($iterable);
        } catch (ExpectationFailedException $e) {
            $this->assertContains(
                $message,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail('An exception should have been thrown');
    }

    private function newPdoStatementWithThreeValues(): \Traversable
    {
        if (!\extension_loaded('pdo') || !\in_array('sqlite', \PDO::getAvailableDrivers())) {
            $this->markTestSkipped('PDO_SQLITE is required');
        }

        $conn = new \PDO('sqlite::memory:');
        $conn->exec('CREATE TABLE foo (id INT NOT NULL PRIMARY KEY)');
        $conn->exec('INSERT INTO foo VALUES (1), (2), (3)');

        return $conn->query('SELECT * FROM foo');
    }
}
