<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use function sprintf;
use ReflectionException;
use RuntimeException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ClassCannotBeInstantiatedException extends RuntimeException implements Exception
{
    public function __construct(string $className, ReflectionException $previous)
    {
        parent::__construct(
            sprintf(
                'Class "%s" cannot be instantiated: %s',
                $className,
                $previous->getMessage()
            ),
            $previous->getCode(),
            $previous
        );
    }
}
