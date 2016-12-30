<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\IncompleteTest;

/**
 * Extension to PHPUnit_Framework_AssertionFailedError to mark the special
 * case of an incomplete test.
 *
 * @since Class available since Release 2.0.0
 */
class PHPUnit_Framework_IncompleteTestError extends AssertionFailedError implements IncompleteTest
{
}
