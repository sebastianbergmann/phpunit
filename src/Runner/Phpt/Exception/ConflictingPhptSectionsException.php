<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Phpt;

use function implode;
use function sprintf;
use PHPUnit\Runner\Exception as RunnerException;
use RuntimeException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ConflictingPhptSectionsException extends RuntimeException implements RunnerException
{
    /**
     * @param non-empty-list<non-empty-string> $sections
     */
    public function __construct(array $sections)
    {
        parent::__construct(
            sprintf(
                'PHPT file must not contain more than one of the sections --%s--',
                implode('--, --', $sections),
            ),
        );
    }
}
