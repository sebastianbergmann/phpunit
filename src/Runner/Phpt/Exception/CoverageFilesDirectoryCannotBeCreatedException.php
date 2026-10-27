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

use function sprintf;
use PHPUnit\Runner\Exception as RunnerException;
use RuntimeException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CoverageFilesDirectoryCannotBeCreatedException extends RuntimeException implements RunnerException
{
    public function __construct(string $directory)
    {
        parent::__construct(
            sprintf(
                'Could not create directory %s for the temporary files that are used to collect code coverage for PHPT file',
                $directory,
            ),
        );
    }
}
