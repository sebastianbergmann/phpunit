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

use function array_any;
use function array_unique;
use function in_array;
use function strtolower;
use Exception;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\MockObject\Rule\MethodName;
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
    private bool $sealed = false;

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

    public function hasMatchers(): bool
    {
        return array_any(
            $this->matchers,
            static fn (Matcher $matcher) => $matcher->hasMatchers(),
        );
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

    /**
     * @throws TestDoubleSealedException
     */
    public function expects(InvocationOrder $rule): InvocationMocker
    {
        if ($this->sealed) {
            throw new TestDoubleSealedException;
        }

        $matcher = new Matcher($rule);
        $this->addMatcher($matcher);

        return new InvocationMockerImplementation(
            $this,
            $matcher,
            ...$this->configurableMethods,
        );
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
    }

    public function seal(bool $isMockObject): void
    {
        if ($this->sealed) {
            return;
        }

        $this->sealed = true;

        if (!$isMockObject) {
            return;
        }

        $configuredMethods = $this->configuredMethodNames();

        foreach ($this->configurableMethods as $method) {
            if (!in_array($method->name(), $configuredMethods, true)) {
                $matcher = new Matcher(new InvokedCount(0));

                $matcher->setMethodNameRule(new MethodName($method->name()));

                $this->addMatcher($matcher);
            }
        }
    }

    public function isSealed(): bool
    {
        return $this->sealed;
    }

    private function addMatcher(Matcher $matcher): void
    {
        $this->matchers[] = $matcher;
    }

    /**
     * Returns the list of method names that have been configured with expectations.
     * Only considers exact string matches for method names.
     * Methods with any() expectation are considered configured.
     *
     * @return list<string>
     */
    private function configuredMethodNames(): array
    {
        $names = [];

        foreach ($this->matchers as $matcher) {
            if (!$matcher->hasMethodNameRule()) {
                continue;
            }

            foreach ($this->configurableMethods as $method) {
                if ($matcher->methodNameRule()->matchesName($method->name())) {
                    $names[] = $method->name();
                }
            }
        }

        return array_unique($names);
    }
}
