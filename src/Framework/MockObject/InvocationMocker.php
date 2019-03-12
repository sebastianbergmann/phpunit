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

use Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker as BuilderInvocationMocker;
use PHPUnit\Framework\MockObject\Builder\Match;
use PHPUnit\Framework\MockObject\Builder\NamespaceMatch;
use PHPUnit\Framework\MockObject\Matcher\DeferredError;
use PHPUnit\Framework\MockObject\Matcher\Invocation as MatcherInvocation;
use PHPUnit\Framework\MockObject\Stub\MatcherCollection;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvocationMocker implements MatcherCollection, Invokable, NamespaceMatch
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

    public function __construct(array $configurableMethods, bool $returnValueGeneration)
    {
        $this->configurableMethods   = $configurableMethods;
        $this->returnValueGeneration = $returnValueGeneration;
    }

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

    public function lookupId($id): Match
    {
        if (isset($this->builderMap[$id])) {
            return $this->builderMap[$id];
        }

        return null;
    }

    /**
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

    public function expects(MatcherInvocation $matcher): BuilderInvocationMocker
    {
        return new BuilderInvocationMocker(
            $this,
            $matcher,
            $this->configurableMethods
        );
    }

    /**
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

        if (!$this->returnValueGeneration) {
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
     * @throws \PHPUnit\Framework\ExpectationFailedException
     */
    public function verify(): void
    {
        foreach ($this->matchers as $matcher) {
            $matcher->verify();
        }
    }
}
