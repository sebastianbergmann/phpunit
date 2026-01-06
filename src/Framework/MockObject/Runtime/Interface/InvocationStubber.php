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

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\MockObject\Stub\Stub;
use Throwable;

interface InvocationStubber
{
    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @param Constraint|non-empty-string|PropertyHook $constraint
     *
     * @return $this
     */
    public function method(Constraint|PropertyHook|string $constraint): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @param non-empty-string $id
     *
     * @return $this
     */
    public function id(string $id): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @param non-empty-string $id
     *
     * @return $this
     */
    public function after(string $id): self;

    /**
     * @return $this
     */
    public function with(mixed ...$arguments): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function withAnyParameters(): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function will(Stub $stub): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function willReturn(mixed $value, mixed ...$nextValues): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function willReturnReference(mixed &$reference): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @param array<int, array<int, mixed>> $valueMap
     *
     * @return $this
     */
    public function willReturnMap(array $valueMap): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function willReturnArgument(int $argumentIndex): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function willReturnCallback(callable $callback): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function willReturnSelf(): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function willReturnOnConsecutiveCalls(mixed ...$values): self;

    /**
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
     *
     * @return $this
     */
    public function willThrowException(Throwable $exception): self;
}
