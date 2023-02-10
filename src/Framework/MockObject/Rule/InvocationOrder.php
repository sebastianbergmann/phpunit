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

use function count;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
use PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\MockObject\Verifiable;
use PHPUnit\Framework\SelfDescribing;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class InvocationOrder implements SelfDescribing, Verifiable
{
    /**
     * @psalm-var list<BaseInvocation>
     */
    private array $invocations = [];
    private ?Matcher $matcher  = null;

    public function numberOfInvocations(): int
    {
        return count($this->invocations);
    }

    public function hasBeenInvoked(): bool
    {
        return count($this->invocations) > 0;
    }

    final public function invoked(BaseInvocation $invocation): void
    {
        $this->invocations[] = $invocation;

        $this->invokedDo($invocation);
    }

    final public function getMatcher(): ?Matcher
    {
        return $this->matcher;
    }

    final public function setMatcher(Matcher $matcher): void
    {
        $this->matcher = $matcher;
    }

    abstract public function matches(BaseInvocation $invocation): bool;

    protected function invokedDo(BaseInvocation $invocation): void
    {
    }
}
