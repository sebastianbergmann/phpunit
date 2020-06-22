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

use SplObjectStorage;
use stdClass;

/**
 * @small
 */
final class TraversableContainsEqualTest extends ConstraintTestCase
{
    public function testArrayContainsFloat(): void
    {
        $constraint = new TraversableContainsEqual(22.04);

        $this->assertTrue($constraint->evaluate([22.04], '', true));
        $this->assertTrue($constraint->evaluate(['22.04'], '', true));
        $this->assertFalse($constraint->evaluate([19.78], '', true));
        $this->assertFalse($constraint->evaluate(['19.78'], '', true));
    }

    public function testArrayContainsInteger(): void
    {
        $constraint = new TraversableContainsEqual(2204);

        $this->assertTrue($constraint->evaluate([2204], '', true));
        $this->assertTrue($constraint->evaluate(['2204'], '', true));
        $this->assertFalse($constraint->evaluate([1978], '', true));
        $this->assertFalse($constraint->evaluate(['1978'], '', true));
    }

    public function testArrayContainsString(): void
    {
        $constraint = new TraversableContainsEqual('foo');

        $this->assertTrue($constraint->evaluate(['foo'], '', true));
        $this->assertFalse($constraint->evaluate(['bar'], '', true));
    }

    public function testArrayContainsObject(): void
    {
        $a      = new stdClass;
        $a->foo = 'bar';

        $b      = new stdClass;
        $b->foo = 'bar';

        $c      = new stdClass;
        $c->foo = 'baz';

        $constraint = new TraversableContainsEqual($a);

        $this->assertTrue($constraint->evaluate([$b], '', true));
        $this->assertFalse($constraint->evaluate([$c], '', true));
    }

    public function test_SplObjectStorage_ContainsObject(): void
    {
        $a      = new stdClass;
        $a->foo = 'bar';

        $b      = new stdClass;
        $b->foo = 'bar';

        $c      = new stdClass;
        $c->foo = 'baz';

        $storageWithA = new SplObjectStorage;
        $storageWithA->attach($a);

        $storageWithoutA = new SplObjectStorage;
        $storageWithoutA->attach($b);

        $constraint = new TraversableContainsEqual($a);

        $this->assertTrue($constraint->evaluate($storageWithA, '', true));
        $this->assertFalse($constraint->evaluate($storageWithoutA, '', true));
    }
}
