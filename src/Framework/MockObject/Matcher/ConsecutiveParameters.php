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
     * @var array
     */
    private $parameterVerificationResults = [];

    /**
     * @param array $parameterGroups
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
     * @param BaseInvocation $invocation
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function matches(BaseInvocation $invocation): bool
    {
        $this->invocations[] = $invocation;
        $callIndex           = \count($this->invocations) - 1;
        $this->parameterVerificationResults[$callIndex] = null;

        if (!$this->shouldInvocationBeVerified($callIndex)) {
            return false;
        }

        $this->parameterVerificationResults[$callIndex] =
            $this->verifyInvocation($invocation, $callIndex);

        return $this->parameterVerificationResults[$callIndex];
    }

    /**
     * Verify all constraints within this expectation
     */
    public function verify(): bool
    {
        foreach ($this->invocations as $callIndex => $invocation) {

            if (!$this->shouldInvocationBeVerified($callIndex)) {
                continue;
            }

            try {
                $this->parameterVerificationResults[$callIndex] = $this->verifyInvocation($invocation, $callIndex);

                return $this->parameterVerificationResults[$callIndex];
            } catch (ExpectationFailedException $e) {
                $this->parameterVerificationResults[$callIndex] = $e;

                throw $this->parameterVerificationResults[$callIndex];
            }
        }
        return true;
    }

    /**
     * Check if the invocation should be verified.
     * As soon as
     *  1. this should be verified to avoid duplicate calls when a callback is specified
     *  2. check is performed for all _and_ individual callbacks
     *
     * to avoid duplication, it was exported into a separate method
     */
    private function shouldInvocationBeVerified(int $callIndex): bool
    {
        // skip check if there is no parameter assertion for this call index and invocation already evaluated
        if (!isset($this->parameterGroups[$callIndex])
            || $this->parameterVerificationResults[$callIndex] !== null
        ) {
            return false;
        }

        return true;
    }

    /**
     * Verify a single invocation
     */
    private function verifyInvocation(?BaseInvocation $invocation, int $callIndex): bool
    {
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

        return true;
    }
}
