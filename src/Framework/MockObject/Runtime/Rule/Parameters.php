<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Rule;

use function array_diff_key;
use function array_is_list;
use function assert;
use function count;
use function is_int;
use function is_string;
use function method_exists;
use function sprintf;
use Exception;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsAnything;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
use PHPUnit\Util\Test;
use ReflectionException;
use ReflectionMethod;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Parameters implements ParametersRule
{
    /**
     * @var array<int|string, Constraint>
     */
    private array $parameters           = [];
    private ?BaseInvocation $invocation = null;
    private null|bool|ExpectationFailedException $parameterVerificationResult;
    private bool $useAssertionCount = true;

    /**
     * @param array<int|string, mixed> $parameters
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct(array $parameters)
    {
        foreach ($parameters as $key => $parameter) {
            if (!$parameter instanceof Constraint) {
                $parameter = new IsEqual(
                    $parameter,
                );
            }

            $this->parameters[$key] = $parameter;
        }
    }

    /**
     * @throws Exception
     */
    public function apply(BaseInvocation $invocation): void
    {
        $this->invocation                  = $invocation;
        $this->parameterVerificationResult = null;

        try {
            $this->parameterVerificationResult = $this->doVerify();
        } catch (ExpectationFailedException $e) {
            $this->parameterVerificationResult = $e;

            throw $this->parameterVerificationResult;
        }
    }

    /**
     * Checks if the invocation $invocation matches the current rules. If it
     * does the rule will get the invoked() method called which should check
     * if an expectation is met.
     *
     * @throws ExpectationFailedException
     */
    public function verify(): void
    {
        $this->doVerify();
    }

    public function useAssertionCount(bool $useAssertionCount): void
    {
        $this->useAssertionCount = $useAssertionCount;
    }

    /**
     * @throws ExpectationFailedException
     */
    private function doVerify(): bool
    {
        if (isset($this->parameterVerificationResult)) {
            return $this->guardAgainstDuplicateEvaluationOfParameterConstraints();
        }

        if ($this->invocation === null) {
            throw new ExpectationFailedException('Doubled method does not exist.');
        }

        $invocation           = $this->invocation;
        $invocationParameters = $invocation->parameters();
        $constraints          = $this->resolveNamedParameters($invocation);

        if (array_diff_key($constraints, $invocationParameters) !== []) {
            $message = 'Parameter count for invocation %s is too low.';

            // The user called `->with($this->anything())`, but may have meant
            // `->withAnyParameters()`.
            //
            // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/199
            if (count($this->parameters) === 1 &&
                isset($this->parameters[0]) &&
                $this->parameters[0]::class === IsAnything::class) {
                $message .= "\nTo allow 0 or more parameters with any value, omit ->with() or use ->withAnyParameters() instead.";
            }

            $this->incrementAssertionCount();

            throw new ExpectationFailedException(
                sprintf($message, $invocation->toString()),
            );
        }

        $parameters = $this->parameters($invocation);

        foreach ($constraints as $i => $parameter) {
            $other = null;

            if ($parameter instanceof Callback && $parameter->isVariadic()) {
                $other = $invocationParameters;
            } elseif (isset($invocationParameters[$i])) {
                $other = $invocationParameters[$i];
            }

            if (isset($parameters[$i])) {
                $parameterName = $parameters[$i];
            } elseif (is_string($i)) {
                $parameterName = '$' . $i;
            } else {
                $parameterName = (string) $i;
            }

            $this->incrementAssertionCount();

            $parameter->evaluate(
                $other,
                sprintf(
                    'Parameter %s for invocation %s does not match expected value.',
                    $parameterName,
                    $invocation->toString(),
                ),
            );
        }

        return true;
    }

    /**
     * Maps the constraints that were configured using named arguments to the
     * position of the parameter with that name. Constraints for named arguments
     * that are collected by a variadic parameter keep their name.
     *
     * @throws ExpectationFailedException
     *
     * @return array<int|string, Constraint>
     */
    private function resolveNamedParameters(BaseInvocation $invocation): array
    {
        if (array_is_list($this->parameters)) {
            return $this->parameters;
        }

        $positions  = [];
        $isVariadic = false;

        if (method_exists($invocation->object(), $invocation->methodName())) {
            foreach (new ReflectionMethod($invocation->object(), $invocation->methodName())->getParameters() as $parameter) {
                if ($parameter->isVariadic()) {
                    $isVariadic = true;

                    continue;
                }

                $positions[$parameter->getName()] = $parameter->getPosition();
            }
        }

        $parameters = [];

        foreach ($this->parameters as $key => $parameter) {
            if (is_int($key)) {
                $parameters[$key] = $parameter;

                continue;
            }

            if (isset($positions[$key])) {
                if (isset($parameters[$positions[$key]])) {
                    $this->incrementAssertionCount();

                    throw new ExpectationFailedException(
                        sprintf(
                            'Named parameter $%s overwrites previous argument for invocation %s.',
                            $key,
                            $invocation->toString(),
                        ),
                    );
                }

                $parameters[$positions[$key]] = $parameter;

                continue;
            }

            if (!$isVariadic) {
                $this->incrementAssertionCount();

                throw new ExpectationFailedException(
                    sprintf(
                        'Unknown named parameter $%s for invocation %s.',
                        $key,
                        $invocation->toString(),
                    ),
                );
            }

            $parameters[$key] = $parameter;
        }

        return $parameters;
    }

    /**
     * @throws ExpectationFailedException
     */
    private function guardAgainstDuplicateEvaluationOfParameterConstraints(): bool
    {
        if ($this->parameterVerificationResult instanceof ExpectationFailedException) {
            throw $this->parameterVerificationResult;
        }

        return (bool) $this->parameterVerificationResult;
    }

    private function incrementAssertionCount(): void
    {
        if ($this->useAssertionCount === false) {
            return;
        }

        Test::currentTestCase()->addToAssertionCount(1);
    }

    /**
     * @return array<non-negative-int, non-empty-string>
     */
    private function parameters(BaseInvocation $invocation): array
    {
        $parameters = [];

        try {
            $reflector = new ReflectionMethod(
                $invocation->className(),
                $invocation->methodName(),
            );

            foreach ($reflector->getParameters() as $parameter) {
                assert($parameter->getPosition() >= 0);

                $parameters[$parameter->getPosition()] = '$' . $parameter->getName();
            }
        } catch (ReflectionException) {
        }

        return $parameters;
    }
}
