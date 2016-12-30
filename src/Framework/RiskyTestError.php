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
use PHPUnit\Framework\RiskyTest;

/**
 * Extension to PHPUnit_Framework_AssertionFailedError to mark the special
 * case of a risky test.
 *
 * @since Class available since Release 4.0.0
 */
class PHPUnit_Framework_RiskyTestError extends AssertionFailedError implements RiskyTest
{
}
