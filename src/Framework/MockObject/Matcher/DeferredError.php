<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Matcher;

use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DeferredError extends StatelessInvocation
{
    /**
     * @var \Throwable
     */
    private $exception;

    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @throws \Throwable
     */
    public function verify(): void
    {
        throw $this->exception;
    }

    public function toString(): string
    {
        return '';
    }

    public function matches(BaseInvocation $invocation): bool
    {
        return true;
    }
}
