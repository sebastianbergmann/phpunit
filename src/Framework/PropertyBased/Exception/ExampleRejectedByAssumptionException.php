<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

/**
 * Thrown by TestCase::assume(), and by TestCase::draw() when no value can be
 * generated, to discard the current example of a property.
 *
 * It extends PHPUnit\Framework\Exception, so that an expectation for an
 * exception, for instance expectException(RuntimeException::class), does not
 * mistake it for the exception that is expected.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExampleRejectedByAssumptionException extends \PHPUnit\Framework\Exception implements Exception
{
}
