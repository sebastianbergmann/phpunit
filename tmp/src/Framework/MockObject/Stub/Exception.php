<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Stubs a method by raising a user-defined exception.
 *
 * @since Class available since Release 1.0.0
 */
class PHPUnit_Framework_MockObject_Stub_Exception implements PHPUnit_Framework_MockObject_Stub
{
    protected $exception;

    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }

    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        throw $this->exception;
    }

    public function toString()
    {
        return sprintf(
            'raise user-specified exception %s',
            PHPUnit_Util_Type::export($this->exception)
        );
    }
}
