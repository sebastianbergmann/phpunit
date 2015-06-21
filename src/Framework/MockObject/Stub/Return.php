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
 * Stubs a method by returning a user-defined value.
 *
 * @since Class available since Release 1.0.0
 */
class PHPUnit_Framework_MockObject_Stub_Return implements PHPUnit_Framework_MockObject_Stub
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function invoke(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        return $this->value;
    }

    public function toString()
    {
        return sprintf(
            'return user-specified value %s',
            PHPUnit_Util_Type::export($this->value)
        );
    }
}
