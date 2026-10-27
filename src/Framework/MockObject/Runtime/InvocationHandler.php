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

use function strtolower;
use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvocationHandler
{
    /**
     * @var list<Matcher>
     */
    private array $matchers = [];

    /**
     * @var array<non-empty-string, Matcher>
     */
    private array $matcherMap = [];

    /**
     * @var list<ConfigurableMethod>
     */
    private readonly array $configurableMethods;
    private readonly bool $returnValueGeneration;
    private readonly bool $isMockObject;
    private ?AssertionFailedError $assertionFailure = null;

    /**
     * @param list<ConfigurableMethod> $configurableMethods
     */
    public function __construct(array $configurableMethods, bool $returnValueGeneration, bool $isMockObject = false)
    {
        $this->configurableMethods   = $configurableMethods;
        $this->returnValueGeneration = $returnValueGeneration;
        $this->isMockObject          = $isMockObject;
    }

    public function isMockObject(): bool
    {
        return $this->isMockObject;
    }

    public function hasInvocationCountRule(): bool
    {
        foreach ($this->matchers as $matcher) {
            if ($matcher->hasInvocationCountRule()) {
                return true;
            }
        }

        return false;
    }

    public function hasParametersRule(): bool
    {
        foreach ($this->matchers as $matcher) {
            if ($matcher->hasParametersRule()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Looks up the match builder with identification $id and returns it.
     *
     * @param non-empty-string $id
     */
    public function lookupMatcher(string $id): ?Matcher
    {
        return $this->matcherMap[$id] ?? null;
    }

    /**
     * Registers a matcher with the identification $id. The matcher can later be
     * looked up using lookupMatcher() to figure out if it has been invoked.
     *
     * @param non-empty-string $id
     *
     * @throws MatcherAlreadyRegisteredException
     */
    public function registerMatcher(string $id, Matcher $matcher): void
    {
        if (isset($this->matcherMap[$id])) {
            throw new MatcherAlreadyRegisteredException($id);
        }

        $this->matcherMap[$id] = $matcher;
    }

    public function expects(InvocationOrder $rule): InvocationStubber
    {
        $matcher = new Matcher($rule);
        $this->addMatcher($matcher);

        return new InvocationStubberImplementation(
            $this,
            $matcher,
            ...$this->configurableMethods,
        );
    }

    /**
     * The matcher is only added once the method name has been configured
     * so that a method that cannot be configured does not leave behind a
     * matcher without a method name rule.
     *
     * @param Constraint|non-empty-string|PropertyHook $constraint
     *
     * @throws InvalidArgumentException
     * @throws MethodCannotBeConfiguredException
     */
    public function method(Constraint|PropertyHook|string $constraint): InvocationStubber
    {
        $matcher = new Matcher(new AnyInvokedCount);

        $stubber = new InvocationStubberImplementation(
            $this,
            $matcher,
            ...$this->configurableMethods,
        );

        $stubber->method($constraint);

        $this->addMatcher($matcher);

        return $stubber;
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws Exception
     */
    public function invoke(Invocation $invocation): mixed
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

                if ($this->assertionFailure === null && $e instanceof AssertionFailedError) {
                    $this->assertionFailure = $e;
                }
            }
        }

        if ($exception !== null) {
            throw $exception;
        }

        if ($hasReturnValue) {
            return $returnValue;
        }

        if (!$this->returnValueGeneration) {
            if (strtolower($invocation->methodName()) === '__tostring') {
                return '';
            }

            throw new ReturnValueNotConfiguredException($invocation);
        }

        return $invocation->generateReturnValue();
    }

    /**
     * @throws Throwable
     */
    public function verify(): void
    {
        foreach ($this->matchers as $matcher) {
            $matcher->verify();
        }

        if ($this->assertionFailure !== null) {
            throw $this->assertionFailure;
        }
    }

    private function addMatcher(Matcher $matcher): void
    {
        $this->matchers[] = $matcher;
    }
}
