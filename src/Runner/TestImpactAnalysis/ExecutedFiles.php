<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

/**
 * The files a test executed at least one line of.
 *
 * Code coverage data reports every line of every file that is subject to code
 * coverage analysis, including the lines that were not executed. Only the
 * files a test executed something in are files that test depends on.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ExecutedFiles
{
    /**
     * @return list<non-empty-string>
     *
     * @phpstan-ignore parameter.internalClass
     */
    public static function in(RawCodeCoverageData $data): array
    {
        $files = [];

        /** @phpstan-ignore method.internalClass */
        foreach ($data->lineCoverage() as $file => $lines) {
            foreach ($lines as $lineStatus) {
                /** @phpstan-ignore classConstant.internalClass */
                if ($lineStatus >= Driver::LINE_EXECUTED) {
                    $files[] = $file;

                    break;
                }
            }
        }

        return $files;
    }
}
