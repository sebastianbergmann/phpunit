<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use function sprintf;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class RiskyDueToUnexpectedAssertionsException extends AssertionFailedError implements RiskyTest
{
    public function __construct(int $numberOfAssertions)
    {
        parent::__construct(
            sprintf(
                'This test is annotated with "@doesNotPerformAssertions" but performed %d assertions',
                $numberOfAssertions
            )
        );
    }
}
