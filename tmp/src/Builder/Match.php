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
 * Builder interface for invocation order matches.
 */
interface PHPUnit_Framework_MockObject_Builder_Match extends PHPUnit_Framework_MockObject_Builder_Stub
{
    /**
     * Defines the expectation which must occur before the current is valid.
     *
     * @param string $id The identification of the expectation that should
     *                   occur before this one.
     *
     * @return PHPUnit_Framework_MockObject_Builder_Stub
     */
    public function after($id);
}
