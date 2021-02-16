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

use function assert;
use function get_class;
use function sprintf;
use function trim;
use PHPUnit\Util\Filter;
use PHPUnit\Util\InvalidDataSetException;
use PHPUnit\Util\Metadata\BackupGlobals;
use PHPUnit\Util\Metadata\BackupStaticProperties;
use PHPUnit\Util\Metadata\PreserveGlobalState;
use PHPUnit\Util\Metadata\Registry as MetadataRegistry;
use PHPUnit\Util\Test as TestUtil;
use ReflectionClass;
use Throwable;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestBuilder
{
    public function build(ReflectionClass $theClass, string $methodName): Test
    {
        $className = $theClass->getName();

        if (!$theClass->isInstantiable()) {
            return new ErrorTestCase(
                sprintf('Cannot instantiate class "%s".', $className)
            );
        }

        try {
            $data = TestUtil::providedData(
                $className,
                $methodName
            );
        } catch (IncompleteTestError $e) {
            $data = new IncompleteTestCase(
                $className,
                $methodName,
                sprintf(
                    "Test for %s::%s marked incomplete by data provider\n%s",
                    $className,
                    $methodName,
                    $this->throwableToString($e)
                )
            );
        } catch (SkippedTestError $e) {
            $data = new SkippedTestCase(
                $className,
                $methodName,
                sprintf(
                    "Test for %s::%s skipped by data provider\n%s",
                    $className,
                    $methodName,
                    $this->throwableToString($e)
                )
            );
        } catch (Throwable $t) {
            $data = new ErrorTestCase(
                sprintf(
                    "The data provider specified for %s::%s is invalid.\n%s",
                    $className,
                    $methodName,
                    $this->throwableToString($t)
                )
            );
        }

        if (isset($data)) {
            $test = $this->buildDataProviderTestSuite(
                $methodName,
                $className,
                $data,
                $this->shouldTestMethodBeRunInSeparateProcess($className, $methodName),
                $this->shouldGlobalStateBePreserved($className, $methodName),
                $this->shouldAllTestMethodsOfTestClassBeRunInSingleSeparateProcess($className),
                $this->backupSettings($className, $methodName)
            );
        } else {
            $test = new $className($methodName);
        }

        if ($test instanceof TestCase) {
            $this->configureTestCase(
                $test,
                $this->shouldTestMethodBeRunInSeparateProcess($className, $methodName),
                $this->shouldGlobalStateBePreserved($className, $methodName),
                $this->shouldAllTestMethodsOfTestClassBeRunInSingleSeparateProcess($className),
                $this->backupSettings($className, $methodName)
            );
        }

        return $test;
    }

    /**
     * @psalm-param class-string $className
     *
     * @param array|ErrorTestCase|IncompleteTestCase|SkippedTestCase $data
     */
    private function buildDataProviderTestSuite(string $methodName, string $className, $data, bool $runTestInSeparateProcess, ?bool $preserveGlobalState, bool $runClassInSeparateProcess, array $backupSettings): DataProviderTestSuite
    {
        $dataProviderTestSuite = new DataProviderTestSuite(
            $className . '::' . $methodName
        );

        $groups = TestUtil::groups($className, $methodName);

        if ($data instanceof ErrorTestCase ||
            $data instanceof SkippedTestCase ||
            $data instanceof IncompleteTestCase) {
            $dataProviderTestSuite->addTest($data, $groups);
        } else {
            foreach ($data as $_dataName => $_data) {
                $_test = new $className($methodName);

                assert($_test instanceof TestCase);

                $_test->setData($_dataName, $_data);

                $this->configureTestCase(
                    $_test,
                    $runTestInSeparateProcess,
                    $preserveGlobalState,
                    $runClassInSeparateProcess,
                    $backupSettings
                );

                $dataProviderTestSuite->addTest($_test, $groups);
            }
        }

        return $dataProviderTestSuite;
    }

    private function configureTestCase(TestCase $test, bool $runTestInSeparateProcess, ?bool $preserveGlobalState, bool $runClassInSeparateProcess, array $backupSettings): void
    {
        if ($runTestInSeparateProcess) {
            $test->setRunTestInSeparateProcess(true);

            if ($preserveGlobalState !== null) {
                $test->setPreserveGlobalState($preserveGlobalState);
            }
        }

        if ($runClassInSeparateProcess) {
            $test->setRunClassInSeparateProcess(true);

            if ($preserveGlobalState !== null) {
                $test->setPreserveGlobalState($preserveGlobalState);
            }
        }

        if ($backupSettings['backupGlobals'] !== null) {
            $test->setBackupGlobals($backupSettings['backupGlobals']);
        }

        if ($backupSettings['backupStaticProperties'] !== null) {
            $test->setBackupStaticAttributes(
                $backupSettings['backupStaticProperties']
            );
        }
    }

    private function throwableToString(Throwable $t): string
    {
        $message = $t->getMessage();

        if (empty(trim($message))) {
            $message = '<no message>';
        }

        if ($t instanceof InvalidDataSetException) {
            return sprintf(
                "%s\n%s",
                $message,
                Filter::getFilteredStacktrace($t)
            );
        }

        return sprintf(
            "%s: %s\n%s",
            get_class($t),
            $message,
            Filter::getFilteredStacktrace($t)
        );
    }

    /**
     * @psalm-param class-string $className
     *
     * @psalm-return array{backupGlobals: ?bool, backupStaticProperties: ?bool}
     */
    private function backupSettings(string $className, string $methodName): array
    {
        $metadataForClass  = MetadataRegistry::reader()->forClass($className);
        $metadataForMethod = MetadataRegistry::reader()->forMethod($className, $methodName);
        $backupGlobals     = null;

        if ($metadataForMethod->isBackupGlobals()->isNotEmpty()) {
            $metadata = $metadataForMethod->isBackupGlobals()->asArray()[0];

            assert($metadata instanceof BackupGlobals);

            if ($metadata->enabled() !== null) {
                $backupGlobals = $metadata->enabled();
            }
        } elseif ($metadataForClass->isBackupGlobals()->isNotEmpty()) {
            $metadata = $metadataForClass->isBackupGlobals()->asArray()[0];

            assert($metadata instanceof BackupGlobals);

            if ($metadata->enabled() !== null) {
                $backupGlobals = $metadata->enabled();
            }
        }

        $backupStaticProperties = null;

        if ($metadataForMethod->isBackupStaticProperties()->isNotEmpty()) {
            $metadata = $metadataForMethod->isBackupStaticProperties()->asArray()[0];

            assert($metadata instanceof BackupStaticProperties);

            if ($metadata->enabled() !== null) {
                $backupStaticProperties = $metadata->enabled();
            }
        } elseif ($metadataForClass->isBackupStaticProperties()->isNotEmpty()) {
            $metadata = $metadataForMethod->isBackupStaticProperties()->asArray()[0];

            assert($metadata instanceof BackupStaticProperties);

            if ($metadata->enabled() !== null) {
                $backupStaticProperties = $metadata->enabled();
            }
        }

        return [
            'backupGlobals'          => $backupGlobals,
            'backupStaticProperties' => $backupStaticProperties,
        ];
    }

    /**
     * @psalm-param class-string $className
     */
    private function shouldGlobalStateBePreserved(string $className, string $methodName): ?bool
    {
        $metadataForMethod = MetadataRegistry::reader()->forMethod($className, $methodName);

        if ($metadataForMethod->isPreserveGlobalState()->isNotEmpty()) {
            $metadata = $metadataForMethod->isPreserveGlobalState()->asArray()[0];

            assert($metadata instanceof PreserveGlobalState);

            return $metadata->enabled();
        }

        $metadataForClass = MetadataRegistry::reader()->forClass($className);

        if ($metadataForClass->isPreserveGlobalState()->isNotEmpty()) {
            $metadata = $metadataForClass->isPreserveGlobalState()->asArray()[0];

            assert($metadata instanceof PreserveGlobalState);

            return $metadata->enabled();
        }

        return null;
    }

    /**
     * @psalm-param class-string $className
     */
    private function shouldTestMethodBeRunInSeparateProcess(string $className, string $methodName): bool
    {
        if (MetadataRegistry::reader()->forClass($className)->isRunTestsInSeparateProcesses()->isNotEmpty()) {
            return true;
        }

        if (MetadataRegistry::reader()->forMethod($className, $methodName)->isRunInSeparateProcess()->isNotEmpty()) {
            return true;
        }

        return false;
    }

    /**
     * @psalm-param class-string $className
     */
    private function shouldAllTestMethodsOfTestClassBeRunInSingleSeparateProcess(string $className): bool
    {
        return MetadataRegistry::reader()->forClass($className)->isRunClassInSeparateProcess()->isNotEmpty();
    }
}
