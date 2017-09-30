<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Stub;

/**
 * Stubs a method by returning a user-defined reference to a value.
 */
class ReturnReference extends ReturnStub
{
    public function __construct(&$value)
    {
        $this->value = &$value;
    }
}
