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

use PHPUnit\Metadata\Api\ProvidedData;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @extends AbstractRepeatTestSuite<TestCase>
 */
final readonly class RepeatTestSuite extends AbstractRepeatTestSuite
{
    public function run(): void
    {
        $defectOccurred = false;

        foreach ($this->tests as $test) {
            if ($defectOccurred) {
                $test->markSkippedForErrorInPreviousRepetition();

                continue;
            }

            $test->run();

            if ($test->status()->isFailure() || $test->status()->isError()) {
                $defectOccurred = true;
            }
        }
    }

    public function name(): string
    {
        return $this->tests[0]::class . '::' . $this->tests[0]->nameWithDataSet();
    }

    /**
     * @param array<ProvidedData> $data
     */
    public function setData(int|string $dataName, array $data): void
    {
        foreach ($this->tests as $test) {
            $test->setData($dataName, $data);
        }
    }

    /**
     * @param list<ExecutionOrderDependency> $dependencies
     */
    public function setDependencies(array $dependencies): void
    {
        foreach ($this->tests as $test) {
            $test->setDependencies($dependencies);
        }
    }
}
