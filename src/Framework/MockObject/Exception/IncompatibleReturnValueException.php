<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class IncompatibleReturnValueException extends \PHPUnit\Framework\Exception implements Exception
{
    /**
     * @param mixed $value
     */
    public function __construct(ConfigurableMethod $method, $value)
    {
        parent::__construct(
            sprintf(
                'Method %s may not return value of type %s, its declared return type is "%s"',
                $method->getName(),
                is_object($value) ? get_class($value) : gettype($value),
                $method->getReturnTypeDeclaration()
            )
        );
    }
}
