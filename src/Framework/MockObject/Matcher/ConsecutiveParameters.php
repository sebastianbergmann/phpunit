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
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;

/**
 * Invocation matcher which looks for sets of specific parameters in the invocations.
 *
 * Checks the parameters of the incoming invocations, the parameter list is
 * checked against the defined constraints in $parameters. If the constraint
 * is met it will return true in matches().
 *
 * It takes a list of match groups and and increases a call index after each invocation.
 * So the first invocation uses the first group of constraints, the second the next and so on.
 */
class ConsecutiveParameters extends StatelessInvocation
{
    /**
     * @var array
     */
    private $parameterGroups = [];

    /**
     * @var array
     */
    private $invocations = [];

    /**
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct(array $parameterGroups)
    {
        foreach ($parameterGroups as $index => $parameters) {
            foreach ($parameters as $parameter) {
                if (!$parameter instanceof Constraint) {
                    $parameter = new IsEqual($parameter);
                }

                $this->parameterGroups[$index][] = $parameter;
            }
        }
    }

    public function toString(): string
    {
        return 'with consecutive parameters';
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     *
     * @return bool
     */
    public function matches(BaseInvocation $invocation)
    {
        $this->invocations[] = $invocation;
        $callIndex           = \count($this->invocations) - 1;

        $this->verifyInvocation($invocation, $callIndex);

        return false;
    }

    public function verify(): void
    {
        foreach ($this->invocations as $callIndex => $invocation) {
            $this->verifyInvocation($invocation, $callIndex);
        }
    }

    /**
     * Verify a single invocation
     *
     * @param int $callIndex
     *
     * @throws ExpectationFailedException
     */
    private function verifyInvocation(BaseInvocation $invocation, $callIndex): void
    {
        if (!isset($this->parameterGroups[$callIndex])) {
            // no parameter assertion for this call index
            return;
        }

        if ($invocation === null) {
            throw new ExpectationFailedException(
                'Mocked method does not exist.'
            );
        }

        $parameters = $this->parameterGroups[$callIndex];

        if (\count($invocation->getParameters()) < \count($parameters)) {
            throw new ExpectationFailedException(
                \sprintf(
                    'Parameter count for invocation %s is too low.',
                    $invocation->toString()
                )
            );
        }

        foreach ($parameters as $i => $parameter) {
            $parameter->evaluate(
                $invocation->getParameters()[$i],
                \sprintf(
                    'Parameter %s for invocation #%d %s does not match expected ' .
                    'value.',
                    $i,
                    $callIndex,
                    $invocation->toString()
                )
            );
        }
    }
}
