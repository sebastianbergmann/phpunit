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

use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\TestCase;

abstract class ConstraintTestCase extends TestCase
{
    final public function testIsCountable()
    {
        $className = $this->className();

        $reflection = new \ReflectionClass($className);

        $this->assertTrue($reflection->implementsInterface(\Countable::class), \sprintf(
            'Failed to assert that "%s" implements "%s".',
            $className,
            \Countable::class
        ));
    }

    final public function testIsSelfDescribing()
    {
        $className = $this->className();

        $reflection = new \ReflectionClass($className);

        $this->assertTrue($reflection->implementsInterface(SelfDescribing::class), \sprintf(
            'Failed to assert that "%s" implements "%s".',
            $className,
            \Countable::class
        ));
    }

    /**
     * Returns the class name of the constraint.
     *
     * @return string
     */
    final protected function className(): string
    {
        return \preg_replace(
            '/Test$/',
            '',
            static::class
        );
    }
}
