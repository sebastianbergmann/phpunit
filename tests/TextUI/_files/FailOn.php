<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
final class FailOn extends PHPUnit\Framework\TestCase
{
    public function testRisky()
    {
        // Always risky, no assertion
    }

    public function testWarning()
    {
        \trigger_error('warning', \E_USER_WARNING);
    }
}
