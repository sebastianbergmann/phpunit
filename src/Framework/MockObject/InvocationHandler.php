<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function sprintf;
use function strtolower;
use Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvocationHandler
{
    /**
     * @var Matcher[]
     */
    private $matchers = [];

    /**
     * @var Matcher[]
     */
    private $matcherMap = [];

    /**
     * @var ConfigurableMethod[]
     */
    private $configurableMethods;

    /**
     * @var bool
     */
    private $returnValueGeneration;

    /**
     * @var Throwable
     */
    private $deferredError;

    public function __construct(array $configurableMethods, bool $returnValueGeneration)
    {
        $this->configurableMethods   = $configurableMethods;
        $this->returnValueGeneration = $returnValueGeneration;
    }

    public function hasMatchers(): bool
    {
        foreach ($this->matchers as $matcher) {
            if ($matcher->hasMatchers()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Looks up the match builder with identification $id and returns it.
     *
     * @param string $id The identification of the match builder
     */
    public function lookupMatcher(string $id): ?Matcher
    {
        if (isset($this->matcherMap[$id])) {
            return $this->matcherMap[$id];
        }

        return null;
    }

    /**
     * Registers a matcher with the identification $id. The matcher can later be
     * looked up using lookupMatcher() to figure out if it has been invoked.
     *
     * @param string  $id      The identification of the matcher
     * @param Matcher $matcher The builder which is being registered
     *
     * @throws RuntimeException
     */
    public function registerMatcher(string $id, Matcher $matcher): void
    {
        if (isset($this->matcherMap[$id])) {
            throw new RuntimeException(
                'Matcher with id <' . $id . '> is already registered.'
            );
        }

        $this->matcherMap[$id] = $matcher;
    }

    public function expects(InvocationOrder $rule): InvocationMocker
    {
        $matcher = new Matcher($rule);
        $this->addMatcher($matcher);

        return new InvocationMocker(
            $this,
            $matcher,
            ...$this->configurableMethods
        );
    }

    /**
     * @throws Exception
     *
     * @return mixed|void
     */
    public function invoke(Invocation $invocation)
    {
        $exception      = null;
        $hasReturnValue = false;
        $returnValue    = null;

        foreach ($this->matchers as $match) {
            try {
                if ($match->matches($invocation)) {
                    $value = $match->invoked($invocation);

                    if (!$hasReturnValue) {
                        $returnValue    = $value;
                        $hasReturnValue = true;
                    }
                }
            } catch (Exception $e) {
                $exception = $e;
            }
        }

        if ($exception !== null) {
            throw $exception;
        }

        if ($hasReturnValue) {
            return $returnValue;
        }

        if (!$this->returnValueGeneration) {
            $exception = new ExpectationFailedException(
                sprintf(
                    'Return value inference disabled and no expectation set up for %s::%s()',
                    $invocation->getClassName(),
                    $invocation->getMethodName()
                )
            );

            if (strtolower($invocation->getMethodName()) === '__tostring') {
                $this->deferredError = $exception;

                return '';
            }

            throw $exception;
        }

        return $invocation->generateReturnValue();
    }

    public function matches(Invocation $invocation): bool
    {
        foreach ($this->matchers as $matcher) {
            if (!$matcher->matches($invocation)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws ExpectationFailedException
     */
    public function verify(): void
    {
        foreach ($this->matchers as $matcher) {
            $matcher->verify();
        }

        if ($this->deferredError) {
            throw $this->deferredError;
        }
    }

    private function addMatcher(Matcher $matcher): void
    {
        $this->matchers[] = $matcher;
    }
}
