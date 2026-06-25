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
use function preg_match;
use function range;
use function sprintf;
use PHPUnit\Event\Facade as EventFacade;
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
use PHPUnit\Metadata\Retry as RetryMetadata;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\Runner\Filter\MethodNameFilterCompiler;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @phpstan-type BackupSettings array{backupGlobals: ?true, backupGlobalsExcludeList: list<string>, backupStaticProperties: ?true, backupStaticPropertiesExcludeList: array<class-string, list<non-empty-string>>}
 */
final readonly class TestBuilder
{
    /**
     * @param ReflectionClass<TestCase> $theClass
     * @param non-empty-string          $methodName
     * @param list<non-empty-string>    $groups
     * @param positive-int              $numberOfRuns
     * @param positive-int              $failureThreshold
     * @param positive-int              $maxAttempts
     *
     * @throws InvalidDataProviderException
     */
    public function build(ReflectionClass $theClass, string $methodName, array $groups = [], int $numberOfRuns = 1, int $failureThreshold = 1, int $maxAttempts = 1): Test
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

            // a method-level #[Repeat] attribute takes precedence over the --retry CLI option
            $maxAttempts = 1;

            if (!$this->hasVoidReturnType($theClass->getMethod($methodName))) {
                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Method %s::%s is annotated with #[Repeat] but does not have a void return type declaration and will not be repeated',
                        $className,
                        $methodName,
                    ),
                );
            }

            if (!$this->doesNotDependOnAnotherTest($className, $methodName)) {
                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Method %s::%s is annotated with #[Repeat] but depends on another test and will not be repeated',
                        $className,
                        $methodName,
                    ),
                );
            }
        }

        $retryMetadata = MetadataRegistry::parser()->forMethod($className, $methodName)->isRetry();

        if ($retryMetadata->isNotEmpty()) {
            if ($repeatMetadata->isNotEmpty()) {
                EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                    sprintf(
                        'Method %s::%s is annotated with both #[Repeat] and #[Retry], the #[Retry] attribute is ignored',
                        $className,
                        $methodName,
                    ),
                );
            } else {
                $metadata = $retryMetadata->asArray()[0];

                assert($metadata instanceof RetryMetadata);

                $maxAttempts = $metadata->maxAttempts();

                if (!$this->hasVoidReturnType($theClass->getMethod($methodName))) {
                    EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                        sprintf(
                            'Method %s::%s is annotated with #[Retry] but does not have a void return type declaration and will not be retried',
                            $className,
                            $methodName,
                        ),
                    );
                }

                if (!$this->doesNotDependOnAnotherTest($className, $methodName)) {
                    EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                        sprintf(
                            'Method %s::%s is annotated with #[Retry] but depends on another test and will not be retried',
                            $className,
                            $methodName,
                        ),
                    );
                }
            }
        }

        $retry = $maxAttempts > 1 &&
                 $this->hasVoidReturnType($theClass->getMethod($methodName)) &&
                 $this->doesNotDependOnAnotherTest($className, $methodName);

        if ($retry) {
            // #[Retry] takes precedence over --repeat
            $numberOfRuns = 1;
        }

        $repeat = $numberOfRuns > 1 &&
                  $this->hasVoidReturnType($theClass->getMethod($methodName)) &&
                  $this->doesNotDependOnAnotherTest($className, $methodName);

        $data = null;

        try {
            ErrorHandler::instance()->enterTestCaseContext($className, $methodName);

            if ($this->requirementsSatisfied($className, $methodName) &&
                !$this->filterExcludesMethod($className, $methodName)) {
                $data = (new DataProvider)->providedData($className, $methodName);
            }
        } finally {
            ErrorHandler::instance()->leaveTestCaseContext();
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
                $retry ? $maxAttempts : 1,
            );
        }

        if ($retry) {
            return $this->buildRetryTestSuite(
                $className,
                $methodName,
                $maxAttempts,
                $runTestInSeparateProcess,
                $preserveGlobalState,
                $backupSettings,
                $groups,
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
                $groups,
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
     * @param positive-int           $maxAttempts
     */
    private function buildDataProviderTestSuite(string $methodName, string $className, array $data, bool $runTestInSeparateProcess, ?bool $preserveGlobalState, array $backupSettings, array $groups, int $numberOfRuns = 1, int $failureThreshold = 1, int $maxAttempts = 1): DataProviderTestSuite
    {
        $dataProviderTestSuite = DataProviderTestSuite::empty(
            $className . '::' . $methodName,
        );

        $groups = array_merge(
            $groups,
            (new Groups)->groups($className, $methodName),
        );

        foreach ($data as $_dataName => $_data) {
            if ($maxAttempts > 1) {
                $factory = function () use ($className, $methodName, $_dataName, $_data, $runTestInSeparateProcess, $preserveGlobalState, $backupSettings): TestCase
                {
                    $test = new $className($methodName);

                    $test->setData($_dataName, $_data->value());

                    $this->configureTestCase(
                        $test,
                        $runTestInSeparateProcess,
                        $preserveGlobalState,
                        $backupSettings,
                    );

                    return $test;
                };

                $dataProviderTestSuite->addTest(
                    RetryTestSuite::fromTestCase(
                        $className . '::' . $methodName . '#' . $_dataName,
                        $factory(),
                        $maxAttempts,
                        $factory,
                        $groups,
                    ),
                    $groups,
                );
            } elseif ($numberOfRuns > 1) {
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
                    RepeatTestSuite::fromTests(
                        $className . '::' . $methodName . '#' . $_dataName,
                        $tests,
                        $failureThreshold,
                        $groups,
                    ),
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
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     * @param positive-int           $numberOfRuns
     * @param positive-int           $failureThreshold
     * @param BackupSettings         $backupSettings
     * @param list<non-empty-string> $groups
     */
    private function buildRepeatTestSuite(string $className, string $methodName, int $numberOfRuns, int $failureThreshold, bool $runTestInSeparateProcess, ?bool $preserveGlobalState, array $backupSettings, array $groups): RepeatTestSuite
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

        $groups = array_merge(
            $groups,
            (new Groups)->groups($className, $methodName),
        );

        return RepeatTestSuite::fromTests(
            $className . '::' . $methodName,
            $tests,
            $failureThreshold,
            $groups,
        );
    }

    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     * @param positive-int           $maxAttempts
     * @param BackupSettings         $backupSettings
     * @param list<non-empty-string> $groups
     */
    private function buildRetryTestSuite(string $className, string $methodName, int $maxAttempts, bool $runTestInSeparateProcess, ?bool $preserveGlobalState, array $backupSettings, array $groups): RetryTestSuite
    {
        $factory = function () use ($className, $methodName, $runTestInSeparateProcess, $preserveGlobalState, $backupSettings): TestCase
        {
            $test = new $className($methodName);

            $this->configureTestCase(
                $test,
                $runTestInSeparateProcess,
                $preserveGlobalState,
                $backupSettings,
            );

            return $test;
        };

        $groups = array_merge(
            $groups,
            (new Groups)->groups($className, $methodName),
        );

        return RetryTestSuite::fromTestCase(
            $className . '::' . $methodName,
            $factory(),
            $maxAttempts,
            $factory,
            $groups,
        );
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

        /** @var array<class-string, list<non-empty-string>> $backupStaticPropertiesExcludeList */
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

    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     */
    private function filterExcludesMethod(string $className, string $methodName): bool
    {
        $configuration = ConfigurationRegistry::get();

        if (!$configuration->hasFilter()) {
            return false;
        }

        $regularExpression = MethodNameFilterCompiler::compile($configuration->filter());

        if ($regularExpression === null) {
            return false;
        }

        $result = @preg_match($regularExpression, $className . '::' . $methodName);

        if ($result === false) {
            return false;
        }

        return $result === 0;
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
