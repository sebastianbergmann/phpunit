<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TimeLimit;

use PHPUnit\Runner\Exception;
use RuntimeException;

/**
 * Thrown into the test that is running when the time limit for the test
 * run is exceeded.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TimeLimitExceededException extends RuntimeException implements Exception
{
}
