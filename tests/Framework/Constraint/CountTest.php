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

use PHPUnit\Framework\TestCase;

class CountTest extends TestCase
{
    public function testCount()
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

    public function testCountDoesNotChangeIteratorKey()
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

    public function testCountGeneratorsDoNotRewind()
    {
        $generatorMaker = new \TestGeneratorMaker;

        $countConstraint = new Count(3);

        $generator = $generatorMaker->create([1, 2, 3]);
        $this->assertEquals(1, $generator->current());
        $countConstraint->evaluate($generator, '', true);
        $this->assertEquals(null, $generator->current());

        $countConstraint = new Count(2);

        $generator = $generatorMaker->create([1, 2, 3]);
        $this->assertEquals(1, $generator->current());
        $generator->next();
        $this->assertEquals(2, $generator->current());
        $countConstraint->evaluate($generator, '', true);
        $this->assertEquals(null, $generator->current());

        $countConstraint = new Count(1);

        $generator = $generatorMaker->create([1, 2, 3]);
        $this->assertEquals(1, $generator->current());
        $generator->next();
        $this->assertEquals(2, $generator->current());
        $generator->next();
        $this->assertEquals(3, $generator->current());
        $countConstraint->evaluate($generator, '', true);
        $this->assertEquals(null, $generator->current());
    }

    public function testCountTraversable()
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
}
