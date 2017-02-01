<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\MockObject\Exception;

/**
 * @since Class available since Release 2.0.6
 */
class PHPUnit_Framework_MockObject_BadMethodCallException extends BadMethodCallException implements Exception
{
}
