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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;

#[CoversClass(ExecutedFiles::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class ExecutedFilesTest extends TestCase
{
    public function testFindsNoFileInEmptyCodeCoverageData(): void
    {
        /** @phpstan-ignore staticMethod.internalClass, argument.type */
        $this->assertSame([], ExecutedFiles::in(RawCodeCoverageData::fromLineCoverage([])));
    }

    public function testFindsTheFilesThatHaveAnExecutedLine(): void
    {
        /** @phpstan-ignore staticMethod.internalClass, argument.type */
        $data = RawCodeCoverageData::fromLineCoverage(
            [
                '/src/Executed.php' => [
                    /** @phpstan-ignore classConstant.internalClass */
                    1 => Driver::LINE_NOT_EXECUTED,
                    /** @phpstan-ignore classConstant.internalClass */
                    2 => Driver::LINE_EXECUTED,
                ],
                '/src/NotExecuted.php' => [
                    /** @phpstan-ignore classConstant.internalClass */
                    1 => Driver::LINE_NOT_EXECUTED,
                    /** @phpstan-ignore classConstant.internalClass */
                    2 => Driver::LINE_NOT_EXECUTABLE,
                ],
                '/src/ExecutedMoreThanOnce.php' => [
                    2 => 3,
                ],
            ],
        );

        /** @phpstan-ignore argument.type */
        $this->assertSame(
            ['/src/Executed.php', '/src/ExecutedMoreThanOnce.php'],
            ExecutedFiles::in($data),
        );
    }
}
