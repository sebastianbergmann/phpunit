<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsAnything;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * Invocation matcher which looks for specific parameters in the invocations.
 *
 * Checks the parameters of all incoming invocations, the parameter list is
 * checked against the defined constraints in $parameters. If the constraint
 * is met it will return true in matches().
 */
class PHPUnit_Framework_MockObject_Matcher_Parameters extends PHPUnit_Framework_MockObject_Matcher_StatelessInvocation
{
    /**
     * @var Constraint[]
     */
    protected $parameters = [];

    /**
     * @var PHPUnit_Framework_MockObject_Invocation
     */
    protected $invocation;

    /**
     * @var ExpectationFailedException
     */
    private $parameterVerificationResult;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        foreach ($parameters as $parameter) {
            if (!($parameter instanceof Constraint)) {
                $parameter = new IsEqual(
                    $parameter
                );
            }

            $this->parameters[] = $parameter;
        }
    }

    /**
     * @return string
     */
    public function toString()
    {
        $text = 'with parameter';

        foreach ($this->parameters as $index => $parameter) {
            if ($index > 0) {
                $text .= ' and';
            }

            $text .= ' ' . $index . ' ' . $parameter->toString();
        }

        return $text;
    }

    /**
     * @param PHPUnit_Framework_MockObject_Invocation $invocation
     *
     * @return bool
     */
    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $this->invocation                  = $invocation;
        $this->parameterVerificationResult = null;

        try {
            $this->parameterVerificationResult = $this->verify();

            return $this->parameterVerificationResult;
        } catch (ExpectationFailedException $e) {
            $this->parameterVerificationResult = $e;

            throw $this->parameterVerificationResult;
        }
    }

    /**
     * Checks if the invocation $invocation matches the current rules. If it
     * does the matcher will get the invoked() method called which should check
     * if an expectation is met.
     *
     * @return bool
     *
     * @throws ExpectationFailedException
     */
    public function verify()
    {
        if (isset($this->parameterVerificationResult)) {
            return $this->guardAgainstDuplicateEvaluationOfParameterConstraints();
        }

        if ($this->invocation === null) {
            throw new ExpectationFailedException(
                'Mocked method does not exist.'
            );
        }

        if (count($this->invocation->parameters) < count($this->parameters)) {
            $message = 'Parameter count for invocation %s is too low.';

            // The user called `->with($this->anything())`, but may have meant
            // `->withAnyParameters()`.
            //
            // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/199
            if (count($this->parameters) === 1 &&
                get_class($this->parameters[0]) === IsAnything::class) {
                $message .= "\nTo allow 0 or more parameters with any value, omit ->with() or use ->withAnyParameters() instead.";
            }

            throw new ExpectationFailedException(
                sprintf($message, $this->invocation->toString())
            );
        }

        foreach ($this->parameters as $i => $parameter) {
            $parameter->evaluate(
                $this->invocation->parameters[$i],
                sprintf(
                    'Parameter %s for invocation %s does not match expected ' .
                    'value.',
                    $i,
                    $this->invocation->toString()
                )
            );
        }

        return true;
    }

    /**
     * @return bool
     *
     * @throws ExpectationFailedException
     */
    private function guardAgainstDuplicateEvaluationOfParameterConstraints()
    {
        if ($this->parameterVerificationResult instanceof Exception) {
            throw $this->parameterVerificationResult;
        }

        return (bool) $this->parameterVerificationResult;
    }
}
