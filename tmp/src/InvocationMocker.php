<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use Exception;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker as BuilderInvocationMocker;
use PHPUnit\Framework\MockObject\Builder\Match;
use PHPUnit\Framework\MockObject\Builder\NamespaceMatch;
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
    private $configurableMethods = [];

    /**
     * @param array $configurableMethods
     */
    public function __construct(array $configurableMethods)
    {
        $this->configurableMethods = $configurableMethods;
    }

    /**
     * @param MatcherInvocation $matcher
     */
    public function addMatcher(MatcherInvocation $matcher)
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
     * @return bool|null
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
    public function registerId($id, Match $builder)
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
     * @return mixed
     *
     * @throws Exception
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

        if (\strtolower($invocation->getMethodName()) === '__tostring') {
            return '';
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
     * @return bool
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function verify()
    {
        foreach ($this->matchers as $matcher) {
            $matcher->verify();
        }
    }
}
