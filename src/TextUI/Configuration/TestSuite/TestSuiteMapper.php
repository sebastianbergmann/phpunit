<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

use PHPUnit\Framework\TestSuite as TestSuiteObject;
use SebastianBergmann\FileIterator\Facade;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestSuiteMapper
{
    public function map(TestSuiteCollection $configuration, string $filter): TestSuiteObject
    {
        $filterAsArray = $filter ? \explode(',', $filter) : [];
        $result        = new TestSuiteObject;

        foreach ($configuration as $testSuiteConfiguration) {
            if (!empty($filterAsArray) && !\in_array($testSuiteConfiguration->name(), $filterAsArray, true)) {
                continue;
            }

            $testSuite      = new TestSuiteObject($testSuiteConfiguration->name());
            $testSuiteEmpty = true;

            foreach ($testSuiteConfiguration->directories() as $directory) {
                if (!\version_compare(\PHP_VERSION, $directory->phpVersion(), $directory->phpVersionOperator()->asString())) {
                    continue;
                }

                $exclude = [];

                foreach ($testSuiteConfiguration->exclude()->asArray() as $file) {
                    $exclude[] = $file->path();
                }

                $testSuite->addTestFiles(
                    (new Facade)->getFilesAsArray(
                        $directory->path(),
                        $directory->suffix(),
                        $directory->prefix(),
                        $exclude
                    )
                );

                $testSuiteEmpty = false;
            }

            foreach ($testSuiteConfiguration->files() as $file) {
                if (!\version_compare(\PHP_VERSION, $file->phpVersion(), $file->phpVersionOperator()->asString())) {
                    continue;
                }

                $testSuite->addTestFile($file->path());

                $testSuiteEmpty = false;
            }

            if (!$testSuiteEmpty) {
                $result->addTest($testSuite);
            }
        }

        return $result;
    }
}
