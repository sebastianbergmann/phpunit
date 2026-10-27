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

use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;
use PHPUnit\Framework\SelfDescribing;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
abstract class InvocationOrder implements SelfDescribing
{
    private int $numberOfInvocations = 0;

    public function numberOfInvocations(): int
    {
        return $this->numberOfInvocations;
    }

    public function hasBeenInvoked(): bool
    {
        return $this->numberOfInvocations > 0;
    }

    final public function invoked(BaseInvocation $invocation): void
    {
        $this->numberOfInvocations++;

        $this->invokedDo($invocation);
    }

    abstract public function verify(): void;

    protected function invokedDo(BaseInvocation $invocation): void
    {
    }
}
