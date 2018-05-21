<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker as BuilderInvocationMocker;
use PHPUnit\Framework\MockObject\Builder\Match;
use PHPUnit\Framework\MockObject\Builder\NamespaceMatch;
use PHPUnit\Framework\MockObject\Matcher\DeferredError;
use PHPUnit\Framework\MockObject\Matcher\Invocation as MatcherInvocation;
use PHPUnit\Framework\MockObject\Stub\MatcherCollection;

/**
 * Mocker for invocations which are sent from
 * MockObject objects.
 *
 * Keeps track of all expectations and stubs as well as registering
 * identifications for builders.
 */
class InvocationMocker implements MatcherCollection, Invokable, NamespaceMatch
{
    /**
     * @var MatcherInvocation[]
     */
    private $matchers = [];

    /**
     * @var Match[]
     */
    private $builderMap = [];

    /**
     * @var string[]
     */
    private $configurableMethods;

    /**
     * @var bool
     */
    private $returnValueGeneration;

    /**
     * @param array $configurableMethods
     * @param bool  $returnValueGeneration
     */
    public function __construct(array $configurableMethods, bool $returnValueGeneration)
    {
        $this->configurableMethods   = $configurableMethods;
        $this->returnValueGeneration = $returnValueGeneration;
    }

    /**
     * @param MatcherInvocation $matcher
     */
    public function addMatcher(MatcherInvocation $matcher): void
    {
        $this->matchers[] = $matcher;
    }

    public function hasMatchers()
    {
        foreach ($this->matchers as $matcher) {
            if ($matcher->hasMatchers()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $id
     *
     * @return null|bool
     */
    public function lookupId($id)
    {
        if (isset($this->builderMap[$id])) {
            return $this->builderMap[$id];
        }

        return;
    }

    /**
     * @param mixed $id
     * @param Match $builder
     *
     * @throws RuntimeException
     */
    public function registerId($id, Match $builder): void
    {
        if (isset($this->builderMap[$id])) {
            throw new RuntimeException(
                'Match builder with id <' . $id . '> is already registered.'
            );
        }

        $this->builderMap[$id] = $builder;
    }

    /**
     * @param MatcherInvocation $matcher
     *
     * @return BuilderInvocationMocker
     */
    public function expects(MatcherInvocation $matcher)
    {
        return new BuilderInvocationMocker(
            $this,
            $matcher,
            $this->configurableMethods
        );
    }

    /**
     * @param Invocation $invocation
     *
     * @throws Exception
     *
     * @return mixed
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

        if ($this->returnValueGeneration === false) {
            $exception = new ExpectationFailedException(
                \sprintf(
                    'Return value inference disabled and no expectation set up for %s::%s()',
                    $invocation->getClassName(),
                    $invocation->getMethodName()
                )
            );

            if (\strtolower($invocation->getMethodName()) === '__tostring') {
                $this->addMatcher(new DeferredError($exception));

                return '';
            }

            throw $exception;
        }

        return $invocation->generateReturnValue();
    }

    /**
     * @param Invocation $invocation
     *
     * @return bool
     */
    public function matches(Invocation $invocation)
    {
        foreach ($this->matchers as $matcher) {
            if (!$matcher->matches($invocation)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     *
     * @return bool
     */
    public function verify()
    {
        foreach ($this->matchers as $matcher) {
            $matcher->verify();
        }
    }
}
