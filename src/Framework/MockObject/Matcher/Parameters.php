<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Matcher;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsAnything;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;

/**
 * Invocation matcher which looks for specific parameters in the invocations.
 *
 * Checks the parameters of all incoming invocations, the parameter list is
 * checked against the defined constraints in $parameters. If the constraint
 * is met it will return true in matches().
 */
class Parameters extends StatelessInvocation
{
    /**
     * @var Constraint[]
     */
    private $parameters = [];

    /**
     * @var BaseInvocation
     */
    private $invocation;

    /**
     * @var ExpectationFailedException
     */
    private $parameterVerificationResult;

    /**
     * @param array $parameters
     *
     * @throws \PHPUnit\Framework\Exception
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
    public function toString(): string
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
     * @param BaseInvocation $invocation
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function matches(BaseInvocation $invocation)
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
     * @throws ExpectationFailedException
     *
     * @return bool
     */
    public function verify()
    {
        if (isset($this->parameterVerificationResult)) {
            return $this->guardAgainstDuplicateEvaluationOfParameterConstraints();
        }

        if ($this->invocation === null) {
            throw new ExpectationFailedException('Mocked method does not exist.');
        }

        if (\count($this->invocation->getParameters()) < \count($this->parameters)) {
            $message = 'Parameter count for invocation %s is too low.';

            // The user called `->with($this->anything())`, but may have meant
            // `->withAnyParameters()`.
            //
            // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/199
            if (\count($this->parameters) === 1 &&
                \get_class($this->parameters[0]) === IsAnything::class) {
                $message .= "\nTo allow 0 or more parameters with any value, omit ->with() or use ->withAnyParameters() instead.";
            }

            throw new ExpectationFailedException(
                \sprintf($message, $this->invocation->toString())
            );
        }

        foreach ($this->parameters as $i => $parameter) {
            $parameter->evaluate(
                $this->invocation->getParameters()[$i],
                \sprintf(
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
     * @throws ExpectationFailedException
     *
     * @return bool
     */
    private function guardAgainstDuplicateEvaluationOfParameterConstraints()
    {
        if ($this->parameterVerificationResult instanceof \Exception) {
            throw $this->parameterVerificationResult;
        }

        return (bool) $this->parameterVerificationResult;
    }
}
