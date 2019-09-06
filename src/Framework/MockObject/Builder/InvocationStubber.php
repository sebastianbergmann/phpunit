<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Builder;

use PHPUnit\Framework\MockObject\Stub\Stub;

/** @internal This class is not covered by the backward compatibility promise for PHPUnit */
interface InvocationStubber
{
    /**
     * @TODO is "will" actually sensible in a stub context? Should a stub produce side-effects?
     *
     * Note: probably yes, since you want a stub of a promise to be able to resolve a real callback, for example
     */
    public function will(Stub $stub): Identity;

    public function willReturn($value, ...$nextValues): self;

    /** @param mixed $reference */
    public function willReturnReference(&$reference): self;

    /** @param array<int, array<int, mixed>> $valueMap */
    public function willReturnMap(array $valueMap): self;

    /** @param int $argumentIndex */
    public function willReturnArgument($argumentIndex): self;

    /** @param callable $callback */
    public function willReturnCallback($callback): self;

    public function willReturnSelf(): self;

    /** @param mixed $values */
    public function willReturnOnConsecutiveCalls(...$values): self;

    public function willThrowException(\Throwable $exception): self;
}
