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

use function sprintf;
use ReflectionClass;

abstract class OperatorTestCase extends ConstraintTestCase
{
    final public function testIsSubclassOfConstraint(): void
    {
        $className = $this->className();

        $reflection = new ReflectionClass($className);

        $this->assertTrue($reflection->isSubclassOf(Constraint::class), sprintf(
            'Failed to assert that "%s" is subclass of "%s".',
            $className,
            Constraint::class
        ));
    }

    abstract public function testOperatorName(): void;

    abstract public function testOperatorPrecedence(): void;

    abstract public function testOperatorArity(): void;

    abstract public function testOperatorCount(): void;
}
