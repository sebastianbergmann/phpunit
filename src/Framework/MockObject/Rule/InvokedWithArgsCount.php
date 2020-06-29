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

use function sprintf;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsAnything;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
use ReflectionClass;

/**
 * Invocation matcher which checks if a method has been invoked a certain amount
 * of times WITH A SPECIFIC SET OF ARGUMENT VALUES.
 * If the number of invocations exceeds the value it will immediately throw an
 * exception,
 * If the number is less it will later be checked in verify() and also throw an
 * exception.
 *
 * @author Mark Blakley <mblakley@datto.com>
 */
class InvokedWithArgsCount extends InvocationOrder
{
    /**
     * @var int
     */
    private $currentCount;

    /**
     * @var int
     */
    private $expectedCount;

    /**
     * @var array
     */
    private $argValues;

    /**
     * @param int   $expectedCount
     * @param array $argValues
     */
    public function __construct($expectedCount, $argValues)
    {
        $this->expectedCount = $expectedCount;
        $this->argValues     = $argValues;
        $this->currentCount  = 0;
    }

    public function toString(): string
    {
        return 'invoked ' . $this->currentCount . ' time(s) with arguments ' . $this->argValuesToString();
    }

    /**
     * @throws ExpectationFailedException
     */
    public function invokedDo(BaseInvocation $invocation): void
    {
        parent::invoked($invocation);

        $this->incrementInvocationWithArgsCount($invocation);

        if ($this->currentCount > $this->expectedCount) {
            $message = $invocation->toString() . ' ';

            switch ($this->expectedCount) {
                case 0:
                    $message .= 'was not expected to be called with arguments ' . $this->argValuesToString() . '.';

                    break;

                case 1:
                    $message .= 'was not expected to be called more than once with arguments ' . $this->argValuesToString() . '.';

                    break;

                default:
                    $message .= sprintf(
                        'was not expected to be called more than %d times with arguments ' . $this->argValuesToString() . '.',
                        $this->expectedCount
                    );
            }

            throw new ExpectationFailedException($message);
        }
    }

    /**
     * Verifies that the current expectation is valid. If everything is OK the
     * code should just return, if not it must throw an exception.
     *
     * @throws ExpectationFailedException
     */
    public function verify(): void
    {
        if ($this->currentCount !== $this->expectedCount) {
            throw new ExpectationFailedException(
                sprintf(
                    'Method was expected to be called %d times with ' . $this->argValuesToString() .
                    ', actually called %d times.',
                    $this->expectedCount,
                    $this->currentCount
                )
            );
        }
    }

    public function matches(BaseInvocation $invocation): bool
    {
        return true;
    }

    private function incrementInvocationWithArgsCount(BaseInvocation $invocation): void
    {
        if (count($this->argValues) > 0) {
            $parameters = $invocation->getParameters();
            // Figure out if it matches the arguments
            if ($parameters === $this->argValues) {
                $this->currentCount++;
            }
            // Figure out if we have some constraints to check
            if (count($parameters) === count($this->argValues)) {
                $matchingParams = 0;

                for ($index = 0; $index < count($this->argValues); $index++) {
                    if ($this->argValues[$index] === $parameters[$index] ||
                            $this->argValues[$index] instanceof IsAnything ||
                            ($this->argValues[$index] instanceof Constraint &&
                            $this->argValues[$index]->evaluate($parameters[$index]))
                        ) {
                        $matchingParams++;
                    }
                }

                if ($matchingParams === count($this->argValues)) {
                    $this->currentCount++;
                }
            }
        } elseif (count($invocation->getParameters()) === 0) {
            $this->currentCount++;
        }
    }

    private function argValuesToString(): string
    {
        $argValuesString = '[';

        foreach ($this->argValues as $argValue) {
            if ($argValue instanceof Constraint) {
                $argValuesString .= (new ReflectionClass($argValue))->getShortName() . ',';
            } elseif (is_array($argValue)) {
                $argValuesString .= '[' . implode(',', $argValue) . '],';
            } else {
                $argValuesString .= '\'' . $argValue . '\',';
            }
        }

        return rtrim($argValuesString, ',') . ']';
    }
}
