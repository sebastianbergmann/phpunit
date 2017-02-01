<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Stubs a method by returning a user-defined reference to a value.
 */
class PHPUnit_Framework_MockObject_Stub_ReturnReference extends PHPUnit_Framework_MockObject_Stub_Return
{
    public function __construct(&$value)
    {
        $this->value = &$value;
    }
}
