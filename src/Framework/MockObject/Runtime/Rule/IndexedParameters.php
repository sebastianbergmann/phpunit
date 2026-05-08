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

use Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class IndexedParameters implements ParametersRule
{
    private Parameters $parameters;

    /**
     * @param list<mixed> $parameters
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct(array $parameters, private int $index, private bool $strict)
    {
        $this->parameters = new Parameters($parameters);
    }

    /**
     * @throws Exception
     */
    public function apply(BaseInvocation $invocation): void
    {
        $this->parameters->apply($invocation);
    }

    /**
     * Checks if the invocation $invocation matches the current rules on underlying parameters.
     *
     * @throws ExpectationFailedException
     */
    public function verify(): void
    {
        $this->parameters->verify();
    }

    public function useAssertionCount(bool $useAssertionCount): void
    {
        $this->parameters->useAssertionCount($useAssertionCount);
    }

    public function at(): int
    {
        return $this->index;
    }

    public function isStrict(): bool
    {
        return $this->strict;
    }
}
