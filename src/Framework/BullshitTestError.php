<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Markus Malkusch <markus@malkusch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extension to PHPUnit_Framework_AssertionFailedError to mark the special
 * case of a bullshit test.
 */
class PHPUnit_Framework_BullshitTestError extends PHPUnit_Framework_AssertionFailedError implements PHPUnit_Framework_BullshitTest
{
}
