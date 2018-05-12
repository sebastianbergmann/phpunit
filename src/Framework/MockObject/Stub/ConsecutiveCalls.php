<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Stub;

use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Stub;
use SebastianBergmann\Exporter\Exporter;

/**
 * Stubs a method by returning a user-defined stack of values.
 */
class ConsecutiveCalls implements Stub
{
    /**
     * @var array
     */
    private $stack;

    /**
     * @var mixed
     */
    private $value;

    public function __construct(array $stack)
    {
        $this->stack = $stack;
    }

    public function invoke(Invocation $invocation)
    {
        $this->value = \array_shift($this->stack);

        if ($this->value instanceof Stub) {
            $this->value = $this->value->invoke($invocation);
        }

        return $this->value;
    }

    public function toString(): string
    {
        $exporter = new Exporter;

        return \sprintf(
            'return user-specified value %s',
            $exporter->export($this->value)
        );
    }
}
