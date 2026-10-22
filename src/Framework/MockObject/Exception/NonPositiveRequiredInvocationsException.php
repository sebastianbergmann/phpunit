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

use function sprintf;
use PHPUnit\Framework\InvalidArgumentException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class NonPositiveRequiredInvocationsException extends InvalidArgumentException implements Exception
{
    public function __construct(int $requiredInvocations)
    {
        parent::__construct(
            sprintf(
                'The argument passed to atLeast() must be a positive integer, %d given',
                $requiredInvocations,
            ),
        );
    }
}
