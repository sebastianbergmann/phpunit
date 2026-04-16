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

use function array_merge;
use function assert;
use function get_parent_class;
use function range;
use PHPUnit\Metadata\Api\DataProvider;
use PHPUnit\Metadata\Api\Groups;
use PHPUnit\Metadata\Api\ProvidedData;
use PHPUnit\Metadata\Api\Requirements;
use PHPUnit\Metadata\BackupGlobals;
use PHPUnit\Metadata\BackupStaticProperties;
use PHPUnit\Metadata\ExcludeGlobalVariableFromBackup;
use PHPUnit\Metadata\ExcludeStaticPropertyFromBackup;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Metadata\PreserveGlobalState;
use PHPUnit\Metadata\Repeat as RepeatMetadata;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @phpstan-type BackupSettings array{backupGlobals: ?true, backupGlobalsExcludeList: list<string>, backupStaticProperties: ?true, backupStaticPropertiesExcludeList: array<string, list<string>>}
 */
final readonly class TestBuilder
{
    /**
     * @param ReflectionClass<TestCase> $theClass
     * @param non-empty-string          $methodName
     * @param list<non-empty-string>    $groups
     * @param positive-int              $numberOfRuns
     * @param positive-int              $failureThreshold
     *
     * @throws InvalidDataProviderException
     */
    public function build(ReflectionClass $theClass, string $methodName, array $groups = [], int $numberOfRuns = 1, int $failureThreshold = 1): Test
    {
        $className                = $theClass->getName();
        $runTestInSeparateProcess = $this->shouldTestMethodBeRunInSeparateProcess($className, $methodName);
        $preserveGlobalState      = $this->shouldGlobalStateBePreserved($className, $methodName);
        $backupSettings           = $this->backupSettings($className, $methodName);

        $repeatMetadata = MetadataRegistry::parser()->forMethod($className, $methodName)->isRepeat();

        if ($repeatMetadata->isNotEmpty()) {
            $metadata = $repeatMetadata->asArray()[0];

            assert($metadata instanceof RepeatMetadata);

            $numberOfRuns     = $metadata->times();
            $failureThreshold = $metadata->failureThreshold();
        }

        $repeat = $numberOfRuns > 1 &&
                  $this->hasVoidReturnType($theClass->getMethod($methodName)) &&
                  $this->doesNotDependOnAnotherTest($className, $methodName);

        $data = null;

        if ($this->requirementsSatisfied($className, $methodName)) {
            try {
                ErrorHandler::instance()->enterTestCaseContext($className, $methodName);

                $data = (new DataProvider)->providedData($className, $methodName);
            } finally {
                ErrorHandler::instance()->leaveTestCaseContext();
            }
        }

        if ($data !== null && $data !== []) {
            return $this->buildDataProviderTestSuite(
                $methodName,
                $className,
                $data,
                $runTestInSeparateProcess,
                $preserveGlobalState,
                $backupSettings,
                $groups,
                $repeat ? $numberOfRuns : 1,
                $failureThreshold,
            );
        }

        if ($repeat) {
            return $this->buildRepeatTestSuite(
                $className,
                $methodName,
                $numberOfRuns,
                $failureThreshold,
                $runTestInSeparateProcess,
                $preserveGlobalState,
                $backupSettings,
            );
        }

        $test = new $className($methodName);

        if ($data === []) {
            $test->setEmptyDataProviderSkipMessage(
                'The data provider for this test provided no data, which is explicitly permitted',
            );
        }

        $this->configureTestCase(
            $test,
            $runTestInSeparateProcess,
            $preserveGlobalState,
            $backupSettings,
        );

        return $test;
    }

    /**
     * @param non-empty-string       $methodName
     * @param class-string<TestCase> $className
     * @param array<ProvidedData>    $data
     * @param BackupSettings         $backupSettings
     * @param list<non-empty-string> $groups
     * @param positive-int           $numberOfRuns
     * @param positive-int           $failureThreshold
     */
    private function buildDataProviderTestSuite(string $methodName, string $className, array $data, bool $runTestInSeparateProcess, ?bool $preserveGlobalState, array $backupSettings, array $groups, int $numberOfRuns = 1, int $failureThreshold = 1): DataProviderTestSuite
    {
        $dataProviderTestSuite = DataProviderTestSuite::empty(
            $className . '::' . $methodName,
        );

        $groups = array_merge(
            $groups,
            (new Groups)->groups($className, $methodName),
        );

        foreach ($data as $_dataName => $_data) {
            if ($numberOfRuns > 1) {
                $tests = [];

                foreach (range(1, $numberOfRuns) as $i) {
                    $_test = new $className($methodName);

                    $_test->setData($_dataName, $_data->value());
                    $_test->setRepetition($i, $numberOfRuns);

                    $this->configureTestCase(
                        $_test,
                        $runTestInSeparateProcess,
                        $preserveGlobalState,
                        $backupSettings,
                    );

                    $tests[] = $_test;
                }

                $dataProviderTestSuite->addTest(
                    new RepeatTestSuite($tests, $failureThreshold),
                    $groups,
                );
            } else {
                $_test = new $className($methodName);

                $_test->setData($_dataName, $_data->value());

                $this->configureTestCase(
                    $_test,
                    $runTestInSeparateProcess,
                    $preserveGlobalState,
                    $backupSettings,
                );

                $dataProviderTestSuite->addTest($_test, $groups);
            }
        }

        return $dataProviderTestSuite;
    }

    /**
     * @param class-string<TestCase>                                                                                                                                            $className
     * @param non-empty-string                                                                                                                                                  $methodName
     * @param positive-int                                                                                                                                                      $numberOfRuns
     * @param positive-int                                                                                                                                                      $failureThreshold
     * @param array{backupGlobals: ?true, backupGlobalsExcludeList: list<string>, backupStaticProperties: ?true, backupStaticPropertiesExcludeList: array<string,list<string>>} $backupSettings
     */
    private function buildRepeatTestSuite(string $className, string $methodName, int $numberOfRuns, int $failureThreshold, bool $runTestInSeparateProcess, ?bool $preserveGlobalState, array $backupSettings): RepeatTestSuite
    {
        $tests = [];

        foreach (range(1, $numberOfRuns) as $i) {
            $test = new $className($methodName);

            $test->setRepetition($i, $numberOfRuns);

            $this->configureTestCase(
                $test,
                $runTestInSeparateProcess,
                $preserveGlobalState,
                $backupSettings,
            );

            $tests[] = $test;
        }

        return new RepeatTestSuite($tests, $failureThreshold);
    }

    /**
     * @param BackupSettings $backupSettings
     */
    private function configureTestCase(TestCase $test, bool $runTestInSeparateProcess, ?bool $preserveGlobalState, array $backupSettings): void
    {
        if ($runTestInSeparateProcess) {
            $test->setRunTestInSeparateProcess(true);
        }

        if ($preserveGlobalState !== null) {
            $test->setPreserveGlobalState($preserveGlobalState);
        }

        if ($backupSettings['backupGlobals'] !== null) {
            $test->setBackupGlobals($backupSettings['backupGlobals']);
        } else {
            $test->setBackupGlobals(ConfigurationRegistry::get()->backupGlobals());
        }

        $test->setBackupGlobalsExcludeList($backupSettings['backupGlobalsExcludeList']);

        if ($backupSettings['backupStaticProperties'] !== null) {
            $test->setBackupStaticProperties($backupSettings['backupStaticProperties']);
        } else {
            $test->setBackupStaticProperties(ConfigurationRegistry::get()->backupStaticProperties());
        }

        /** @var array<string, list<class-string>> $backupStaticPropertiesExcludeList */
        $backupStaticPropertiesExcludeList = $backupSettings['backupStaticPropertiesExcludeList'];

        $test->setBackupStaticPropertiesExcludeList($backupStaticPropertiesExcludeList);
    }

    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     *
     * @return BackupSettings
     */
    private function backupSettings(string $className, string $methodName): array
    {
        $metadataForClass          = MetadataRegistry::parser()->forClass($className);
        $metadataForMethod         = MetadataRegistry::parser()->forMethod($className, $methodName);
        $metadataForClassAndMethod = MetadataRegistry::parser()->forClassAndMethod($className, $methodName);

        $backupGlobals            = null;
        $backupGlobalsExcludeList = [];

        if ($metadataForMethod->isBackupGlobals()->isNotEmpty()) {
            $metadata = $metadataForMethod->isBackupGlobals()->asArray()[0];

            assert($metadata instanceof BackupGlobals);

            if ($metadata->enabled()) {
                $backupGlobals = true;
            }
        } elseif ($metadataForClass->isBackupGlobals()->isNotEmpty()) {
            $metadata = $metadataForClass->isBackupGlobals()->asArray()[0];

            assert($metadata instanceof BackupGlobals);

            if ($metadata->enabled()) {
                $backupGlobals = true;
            }
        }

        foreach ($metadataForClassAndMethod->isExcludeGlobalVariableFromBackup() as $metadata) {
            assert($metadata instanceof ExcludeGlobalVariableFromBackup);

            $backupGlobalsExcludeList[] = $metadata->globalVariableName();
        }

        $backupStaticProperties            = null;
        $backupStaticPropertiesExcludeList = [];

        if ($metadataForMethod->isBackupStaticProperties()->isNotEmpty()) {
            $metadata = $metadataForMethod->isBackupStaticProperties()->asArray()[0];

            assert($metadata instanceof BackupStaticProperties);

            if ($metadata->enabled()) {
                $backupStaticProperties = true;
            }
        } elseif ($metadataForClass->isBackupStaticProperties()->isNotEmpty()) {
            $metadata = $metadataForClass->isBackupStaticProperties()->asArray()[0];

            assert($metadata instanceof BackupStaticProperties);

            if ($metadata->enabled()) {
                $backupStaticProperties = true;
            }
        }

        foreach ($metadataForClassAndMethod->isExcludeStaticPropertyFromBackup() as $metadata) {
            assert($metadata instanceof ExcludeStaticPropertyFromBackup);

            if (!isset($backupStaticPropertiesExcludeList[$metadata->className()])) {
                $backupStaticPropertiesExcludeList[$metadata->className()] = [];
            }

            $backupStaticPropertiesExcludeList[$metadata->className()][] = $metadata->propertyName();
        }

        return [
            'backupGlobals'                     => $backupGlobals,
            'backupGlobalsExcludeList'          => $backupGlobalsExcludeList,
            'backupStaticProperties'            => $backupStaticProperties,
            'backupStaticPropertiesExcludeList' => $backupStaticPropertiesExcludeList,
        ];
    }

    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     */
    private function shouldGlobalStateBePreserved(string $className, string $methodName): ?bool
    {
        $metadataForMethod = MetadataRegistry::parser()->forMethod($className, $methodName);

        if ($metadataForMethod->isPreserveGlobalState()->isNotEmpty()) {
            $metadata = $metadataForMethod->isPreserveGlobalState()->asArray()[0];

            assert($metadata instanceof PreserveGlobalState);

            return $metadata->enabled();
        }

        $metadataForClass = MetadataRegistry::parser()->forClass($className);

        if ($metadataForClass->isPreserveGlobalState()->isNotEmpty()) {
            $metadata = $metadataForClass->isPreserveGlobalState()->asArray()[0];

            assert($metadata instanceof PreserveGlobalState);

            return $metadata->enabled();
        }

        return null;
    }

    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     */
    private function shouldTestMethodBeRunInSeparateProcess(string $className, string $methodName): bool
    {
        $class = $className;

        do {
            if (MetadataRegistry::parser()->forClass($class)->isRunTestsInSeparateProcesses()->isNotEmpty()) {
                return true;
            }
        } while (($class = get_parent_class($class)) !== false);

        if (MetadataRegistry::parser()->forMethod($className, $methodName)->isRunInSeparateProcess()->isNotEmpty()) {
            return true;
        }

        return false;
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    private function requirementsSatisfied(string $className, string $methodName): bool
    {
        return (new Requirements)->requirementsNotSatisfiedFor($className, $methodName) === [];
    }

    private function hasVoidReturnType(ReflectionMethod $method): bool
    {
        if (!$method->hasReturnType()) {
            return false;
        }

        $returnType = $method->getReturnType();

        if (!$returnType instanceof ReflectionNamedType) {
            return false;
        }

        return $returnType->getName() === 'void';
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    private function doesNotDependOnAnotherTest(string $className, string $methodName): bool
    {
        $metadata = MetadataRegistry::parser()->forClassAndMethod($className, $methodName);

        return $metadata->isDepends()->isEmpty();
    }
}
