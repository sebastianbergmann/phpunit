<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Matcher;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
use PHPUnit\Util\InvalidArgumentHelper;

/**
 * Invocation matcher which looks for a specific method name in the invocations.
 *
 * Checks the method name all incoming invocations, the name is checked against
 * the defined constraint $constraint. If the constraint is met it will return
 * true in matches().
 */
class MethodName extends StatelessInvocation
{
    /**
     * @var Constraint
     */
    private $constraint;

    /**
     * @param  Constraint|string
     *
     * @throws Constraint
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct($constraint)
    {
        if (!$constraint instanceof Constraint) {
            if (!\is_string($constraint)) {
                throw InvalidArgumentHelper::factory(1, 'string');
            }

            $constraint = new IsEqual(
                $constraint,
                0,
                10,
                false,
                true
            );
        }

        $this->constraint = $constraint;
    }

    public function toString(): string
    {
        return 'method name ' . $this->constraint->toString();
    }

    /**
     * @return bool
     */
    public function matches(BaseInvocation $invocation)
    {
        return $this->constraint->evaluate($invocation->getMethodName(), '', true);
    }
}
