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
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
interface InvocationStubber
{
    public function will(Stub $stub): Identity;

    /** @return self */
    public function willReturn($value, ...$nextValues)/* : self */;

    /**
     * @param mixed $reference
     *
     * @return self
     */
    public function willReturnReference(&$reference)/* : self */;

    /**
     * @param array<int, array<int, mixed>> $valueMap
     *
     * @return self
     */
    public function willReturnMap(array $valueMap)/* : self */;

    /**
     * @param int $argumentIndex
     *
     * @return self
     */
    public function willReturnArgument($argumentIndex)/* : self */;

    /**
     * @param callable $callback
     *
     * @return self
     */
    public function willReturnCallback($callback)/* : self */;

    /** @return self */
    public function willReturnSelf()/* : self */;

    /**
     * @param mixed $values
     *
     * @return self
     */
    public function willReturnOnConsecutiveCalls(...$values)/* : self */;

    /** @return self */
    public function willThrowException(Throwable $exception)/* : self */;
}
