<?php declare(strict_types=1);
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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ReturnCallback implements Stub
{
    private $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function invoke(Invocation $invocation)
    {
        return \call_user_func_array($this->callback, $invocation->getParameters());
    }

    public function toString(): string
    {
        if (\is_array($this->callback)) {
            if (\is_object($this->callback[0])) {
                $class = \get_class($this->callback[0]);
                $type  = '->';
            } else {
                $class = $this->callback[0];
                $type  = '::';
            }

            return \sprintf(
                'return result of user defined callback %s%s%s() with the ' .
                'passed arguments',
                $class,
                $type,
                $this->callback[1]
            );
        }

        return 'return result of user defined callback ' . $this->callback .
               ' with the passed arguments';
    }
}
