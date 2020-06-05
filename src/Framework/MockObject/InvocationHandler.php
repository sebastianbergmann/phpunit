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

use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;

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
     * @var \Throwable
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
     * @throws RuntimeException
     * @throws \Exception
     */
    public function invoke(Invocation $invocation)
    {
        $exception      = null;
        $returnValue    = null;
        $match          = $this->findMatcher($invocation);

        if ($match !== null) {
            return $match->invoked($invocation);
        }

        if (!$this->returnValueGeneration) {
            $exception = new RuntimeException(
                \sprintf(
                    'Return value inference disabled and no expectation set up for %s::%s()',
                    $invocation->getClassName(),
                    $invocation->getMethodName()
                )
            );

            if (\strtolower($invocation->getMethodName()) === '__tostring') {
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
     * @throws \Throwable
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

    /**
     * @throws RuntimeException
     */
    private function findMatcher(Invocation $invocation): ?Matcher
    {
        $result = [];

        foreach ($this->matchers as $matcher) {
            if ($matcher->matches($invocation)) {
                $result[] = $matcher;
            }
        }

        if (\count($result) > 1) {
            throw new RuntimeException(
                \sprintf(
                    'More than one invocation handler has been configured for %s::%s()',
                    $invocation->getClassName(),
                    $invocation->getMethodName()
                )
            );
        }

        return \current($result) ?: null;
    }

    private function addMatcher(Matcher $matcher): void
    {
        $this->matchers[] = $matcher;
    }
}
