<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class PHPUnit
{
    /**
     * @var bool
     */
    private $cacheResult;

    /**
     * @var ?string
     */
    private $cacheResultFile;

    /**
     * @var int|string
     */
    private $columns;

    /**
     * @var string
     */
    private $colors;

    /**
     * @var bool
     */
    private $stderr;

    /**
     * @var bool
     */
    private $noInteraction;

    /**
     * @var bool
     */
    private $verbose;

    /**
     * @var bool
     */
    private $reverseDefectList;

    /**
     * @var bool
     */
    private $convertDeprecationsToExceptions;

    /**
     * @var bool
     */
    private $convertErrorsToExceptions;

    /**
     * @var bool
     */
    private $convertNoticesToExceptions;

    /**
     * @var bool
     */
    private $convertWarningsToExceptions;

    /**
     * @var bool
     */
    private $forceCoversAnnotation;

    /**
     * @var ?string
     */
    private $bootstrap;

    /**
     * @var bool
     */
    private $processIsolation;

    /**
     * @var bool
     */
    private $failOnEmptyTestSuite;

    /**
     * @var bool
     */
    private $failOnIncomplete;

    /**
     * @var bool
     */
    private $failOnRisky;

    /**
     * @var bool
     */
    private $failOnSkipped;

    /**
     * @var bool
     */
    private $failOnWarning;

    /**
     * @var bool
     */
    private $stopOnDefect;

    /**
     * @var bool
     */
    private $stopOnError;

    /**
     * @var bool
     */
    private $stopOnFailure;

    /**
     * @var bool
     */
    private $stopOnWarning;

    /**
     * @var bool
     */
    private $stopOnIncomplete;

    /**
     * @var bool
     */
    private $stopOnRisky;

    /**
     * @var bool
     */
    private $stopOnSkipped;

    /**
     * @var ?string
     */
    private $extensionsDirectory;

    /**
     * @var ?string
     *
     * @deprecated see https://github.com/sebastianbergmann/phpunit/issues/4039
     */
    private $testSuiteLoaderClass;

    /**
     * @var ?string
     *
     * @deprecated see https://github.com/sebastianbergmann/phpunit/issues/4039
     */
    private $testSuiteLoaderFile;

    /**
     * @var ?string
     */
    private $printerClass;

    /**
     * @var ?string
     */
    private $printerFile;

    /**
     * @var bool
     */
    private $beStrictAboutChangesToGlobalState;

    /**
     * @var bool
     */
    private $beStrictAboutOutputDuringTests;

    /**
     * @var bool
     */
    private $beStrictAboutResourceUsageDuringSmallTests;

    /**
     * @var bool
     */
    private $beStrictAboutTestsThatDoNotTestAnything;

    /**
     * @var bool
     */
    private $beStrictAboutTodoAnnotatedTests;

    /**
     * @var bool
     */
    private $beStrictAboutCoversAnnotation;

    /**
     * @var bool
     */
    private $enforceTimeLimit;

    /**
     * @var int
     */
    private $defaultTimeLimit;

    /**
     * @var int
     */
    private $timeoutForSmallTests;

    /**
     * @var int
     */
    private $timeoutForMediumTests;

    /**
     * @var int
     */
    private $timeoutForLargeTests;

    /**
     * @var ?string
     */
    private $defaultTestSuite;

    /**
     * @var int
     */
    private $executionOrder;

    /**
     * @var bool
     */
    private $resolveDependencies;

    /**
     * @var bool
     */
    private $defectsFirst;

    /**
     * @var bool
     */
    private $backupGlobals;

    /**
     * @var bool
     */
    private $backupStaticAttributes;

    /**
     * @var bool
     */
    private $registerMockObjectsFromTestArgumentsRecursively;

    /**
     * @var bool
     */
    private $conflictBetweenPrinterClassAndTestdox;

    public function __construct(bool $cacheResult, ?string $cacheResultFile, $columns, string $colors, bool $stderr, bool $noInteraction, bool $verbose, bool $reverseDefectList, bool $convertDeprecationsToExceptions, bool $convertErrorsToExceptions, bool $convertNoticesToExceptions, bool $convertWarningsToExceptions, bool $forceCoversAnnotation, ?string $bootstrap, bool $processIsolation, bool $failOnEmptyTestSuite, bool $failOnIncomplete, bool $failOnRisky, bool $failOnSkipped, bool $failOnWarning, bool $stopOnDefect, bool $stopOnError, bool $stopOnFailure, bool $stopOnWarning, bool $stopOnIncomplete, bool $stopOnRisky, bool $stopOnSkipped, ?string $extensionsDirectory, ?string $testSuiteLoaderClass, ?string $testSuiteLoaderFile, ?string $printerClass, ?string $printerFile, bool $beStrictAboutChangesToGlobalState, bool $beStrictAboutOutputDuringTests, bool $beStrictAboutResourceUsageDuringSmallTests, bool $beStrictAboutTestsThatDoNotTestAnything, bool $beStrictAboutTodoAnnotatedTests, bool $beStrictAboutCoversAnnotation, bool $enforceTimeLimit, int $defaultTimeLimit, int $timeoutForSmallTests, int $timeoutForMediumTests, int $timeoutForLargeTests, ?string $defaultTestSuite, int $executionOrder, bool $resolveDependencies, bool $defectsFirst, bool $backupGlobals, bool $backupStaticAttributes, bool $registerMockObjectsFromTestArgumentsRecursively, bool $conflictBetweenPrinterClassAndTestdox)
    {
        $this->cacheResult                                     = $cacheResult;
        $this->cacheResultFile                                 = $cacheResultFile;
        $this->columns                                         = $columns;
        $this->colors                                          = $colors;
        $this->stderr                                          = $stderr;
        $this->noInteraction                                   = $noInteraction;
        $this->verbose                                         = $verbose;
        $this->reverseDefectList                               = $reverseDefectList;
        $this->convertDeprecationsToExceptions                 = $convertDeprecationsToExceptions;
        $this->convertErrorsToExceptions                       = $convertErrorsToExceptions;
        $this->convertNoticesToExceptions                      = $convertNoticesToExceptions;
        $this->convertWarningsToExceptions                     = $convertWarningsToExceptions;
        $this->forceCoversAnnotation                           = $forceCoversAnnotation;
        $this->bootstrap                                       = $bootstrap;
        $this->processIsolation                                = $processIsolation;
        $this->failOnEmptyTestSuite                            = $failOnEmptyTestSuite;
        $this->failOnIncomplete                                = $failOnIncomplete;
        $this->failOnRisky                                     = $failOnRisky;
        $this->failOnSkipped                                   = $failOnSkipped;
        $this->failOnWarning                                   = $failOnWarning;
        $this->stopOnDefect                                    = $stopOnDefect;
        $this->stopOnError                                     = $stopOnError;
        $this->stopOnFailure                                   = $stopOnFailure;
        $this->stopOnWarning                                   = $stopOnWarning;
        $this->stopOnIncomplete                                = $stopOnIncomplete;
        $this->stopOnRisky                                     = $stopOnRisky;
        $this->stopOnSkipped                                   = $stopOnSkipped;
        $this->extensionsDirectory                             = $extensionsDirectory;
        $this->testSuiteLoaderClass                            = $testSuiteLoaderClass;
        $this->testSuiteLoaderFile                             = $testSuiteLoaderFile;
        $this->printerClass                                    = $printerClass;
        $this->printerFile                                     = $printerFile;
        $this->beStrictAboutChangesToGlobalState               = $beStrictAboutChangesToGlobalState;
        $this->beStrictAboutOutputDuringTests                  = $beStrictAboutOutputDuringTests;
        $this->beStrictAboutResourceUsageDuringSmallTests      = $beStrictAboutResourceUsageDuringSmallTests;
        $this->beStrictAboutTestsThatDoNotTestAnything         = $beStrictAboutTestsThatDoNotTestAnything;
        $this->beStrictAboutTodoAnnotatedTests                 = $beStrictAboutTodoAnnotatedTests;
        $this->beStrictAboutCoversAnnotation                   = $beStrictAboutCoversAnnotation;
        $this->enforceTimeLimit                                = $enforceTimeLimit;
        $this->defaultTimeLimit                                = $defaultTimeLimit;
        $this->timeoutForSmallTests                            = $timeoutForSmallTests;
        $this->timeoutForMediumTests                           = $timeoutForMediumTests;
        $this->timeoutForLargeTests                            = $timeoutForLargeTests;
        $this->defaultTestSuite                                = $defaultTestSuite;
        $this->executionOrder                                  = $executionOrder;
        $this->resolveDependencies                             = $resolveDependencies;
        $this->defectsFirst                                    = $defectsFirst;
        $this->backupGlobals                                   = $backupGlobals;
        $this->backupStaticAttributes                          = $backupStaticAttributes;
        $this->registerMockObjectsFromTestArgumentsRecursively = $registerMockObjectsFromTestArgumentsRecursively;
        $this->conflictBetweenPrinterClassAndTestdox           = $conflictBetweenPrinterClassAndTestdox;
    }

    public function cacheResult(): bool
    {
        return $this->cacheResult;
    }

    /**
     * @psalm-assert-if-true !null $this->cacheResultFile
     */
    public function hasCacheResultFile(): bool
    {
        return $this->cacheResultFile !== null;
    }

    /**
     * @throws Exception
     */
    public function cacheResultFile(): string
    {
        if (!$this->hasCacheResultFile()) {
            throw new Exception('Cache result file is not configured');
        }

        return (string) $this->cacheResultFile;
    }

    public function columns()
    {
        return $this->columns;
    }

    public function colors(): string
    {
        return $this->colors;
    }

    public function stderr(): bool
    {
        return $this->stderr;
    }

    public function noInteraction(): bool
    {
        return $this->noInteraction;
    }

    public function verbose(): bool
    {
        return $this->verbose;
    }

    public function reverseDefectList(): bool
    {
        return $this->reverseDefectList;
    }

    public function convertDeprecationsToExceptions(): bool
    {
        return $this->convertDeprecationsToExceptions;
    }

    public function convertErrorsToExceptions(): bool
    {
        return $this->convertErrorsToExceptions;
    }

    public function convertNoticesToExceptions(): bool
    {
        return $this->convertNoticesToExceptions;
    }

    public function convertWarningsToExceptions(): bool
    {
        return $this->convertWarningsToExceptions;
    }

    public function forceCoversAnnotation(): bool
    {
        return $this->forceCoversAnnotation;
    }

    /**
     * @psalm-assert-if-true !null $this->bootstrap
     */
    public function hasBootstrap(): bool
    {
        return $this->bootstrap !== null;
    }

    /**
     * @throws Exception
     */
    public function bootstrap(): string
    {
        if (!$this->hasBootstrap()) {
            throw new Exception('Bootstrap script is not configured');
        }

        return (string) $this->bootstrap;
    }

    public function processIsolation(): bool
    {
        return $this->processIsolation;
    }

    public function failOnEmptyTestSuite(): bool
    {
        return $this->failOnEmptyTestSuite;
    }

    public function failOnIncomplete(): bool
    {
        return $this->failOnIncomplete;
    }

    public function failOnRisky(): bool
    {
        return $this->failOnRisky;
    }

    public function failOnSkipped(): bool
    {
        return $this->failOnSkipped;
    }

    public function failOnWarning(): bool
    {
        return $this->failOnWarning;
    }

    public function stopOnDefect(): bool
    {
        return $this->stopOnDefect;
    }

    public function stopOnError(): bool
    {
        return $this->stopOnError;
    }

    public function stopOnFailure(): bool
    {
        return $this->stopOnFailure;
    }

    public function stopOnWarning(): bool
    {
        return $this->stopOnWarning;
    }

    public function stopOnIncomplete(): bool
    {
        return $this->stopOnIncomplete;
    }

    public function stopOnRisky(): bool
    {
        return $this->stopOnRisky;
    }

    public function stopOnSkipped(): bool
    {
        return $this->stopOnSkipped;
    }

    /**
     * @psalm-assert-if-true !null $this->extensionsDirectory
     */
    public function hasExtensionsDirectory(): bool
    {
        return $this->extensionsDirectory !== null;
    }

    /**
     * @throws Exception
     */
    public function extensionsDirectory(): string
    {
        if (!$this->hasExtensionsDirectory()) {
            throw new Exception('Extensions directory is not configured');
        }

        return (string) $this->extensionsDirectory;
    }

    /**
     * @psalm-assert-if-true !null $this->testSuiteLoaderClass
     *
     * @deprecated see https://github.com/sebastianbergmann/phpunit/issues/4039
     */
    public function hasTestSuiteLoaderClass(): bool
    {
        return $this->testSuiteLoaderClass !== null;
    }

    /**
     * @throws Exception
     *
     * @deprecated see https://github.com/sebastianbergmann/phpunit/issues/4039
     */
    public function testSuiteLoaderClass(): string
    {
        if (!$this->hasTestSuiteLoaderClass()) {
            throw new Exception('TestSuiteLoader class is not configured');
        }

        return (string) $this->testSuiteLoaderClass;
    }

    /**
     * @psalm-assert-if-true !null $this->testSuiteLoaderFile
     *
     * @deprecated see https://github.com/sebastianbergmann/phpunit/issues/4039
     */
    public function hasTestSuiteLoaderFile(): bool
    {
        return $this->testSuiteLoaderFile !== null;
    }

    /**
     * @throws Exception
     *
     * @deprecated see https://github.com/sebastianbergmann/phpunit/issues/4039
     */
    public function testSuiteLoaderFile(): string
    {
        if (!$this->hasTestSuiteLoaderFile()) {
            throw new Exception('TestSuiteLoader sourcecode file is not configured');
        }

        return (string) $this->testSuiteLoaderFile;
    }

    /**
     * @psalm-assert-if-true !null $this->printerClass
     */
    public function hasPrinterClass(): bool
    {
        return $this->printerClass !== null;
    }

    /**
     * @throws Exception
     */
    public function printerClass(): string
    {
        if (!$this->hasPrinterClass()) {
            throw new Exception('ResultPrinter class is not configured');
        }

        return (string) $this->printerClass;
    }

    /**
     * @psalm-assert-if-true !null $this->printerFile
     */
    public function hasPrinterFile(): bool
    {
        return $this->printerFile !== null;
    }

    /**
     * @throws Exception
     */
    public function printerFile(): string
    {
        if (!$this->hasPrinterFile()) {
            throw new Exception('ResultPrinter sourcecode file is not configured');
        }

        return (string) $this->printerFile;
    }

    public function beStrictAboutChangesToGlobalState(): bool
    {
        return $this->beStrictAboutChangesToGlobalState;
    }

    public function beStrictAboutOutputDuringTests(): bool
    {
        return $this->beStrictAboutOutputDuringTests;
    }

    public function beStrictAboutResourceUsageDuringSmallTests(): bool
    {
        return $this->beStrictAboutResourceUsageDuringSmallTests;
    }

    public function beStrictAboutTestsThatDoNotTestAnything(): bool
    {
        return $this->beStrictAboutTestsThatDoNotTestAnything;
    }

    public function beStrictAboutTodoAnnotatedTests(): bool
    {
        return $this->beStrictAboutTodoAnnotatedTests;
    }

    public function beStrictAboutCoversAnnotation(): bool
    {
        return $this->beStrictAboutCoversAnnotation;
    }

    public function enforceTimeLimit(): bool
    {
        return $this->enforceTimeLimit;
    }

    public function defaultTimeLimit(): int
    {
        return $this->defaultTimeLimit;
    }

    public function timeoutForSmallTests(): int
    {
        return $this->timeoutForSmallTests;
    }

    public function timeoutForMediumTests(): int
    {
        return $this->timeoutForMediumTests;
    }

    public function timeoutForLargeTests(): int
    {
        return $this->timeoutForLargeTests;
    }

    /**
     * @psalm-assert-if-true !null $this->defaultTestSuite
     */
    public function hasDefaultTestSuite(): bool
    {
        return $this->defaultTestSuite !== null;
    }

    /**
     * @throws Exception
     */
    public function defaultTestSuite(): string
    {
        if (!$this->hasDefaultTestSuite()) {
            throw new Exception('Default test suite is not configured');
        }

        return (string) $this->defaultTestSuite;
    }

    public function executionOrder(): int
    {
        return $this->executionOrder;
    }

    public function resolveDependencies(): bool
    {
        return $this->resolveDependencies;
    }

    public function defectsFirst(): bool
    {
        return $this->defectsFirst;
    }

    public function backupGlobals(): bool
    {
        return $this->backupGlobals;
    }

    public function backupStaticAttributes(): bool
    {
        return $this->backupStaticAttributes;
    }

    public function registerMockObjectsFromTestArgumentsRecursively(): bool
    {
        return $this->registerMockObjectsFromTestArgumentsRecursively;
    }

    public function conflictBetweenPrinterClassAndTestdox(): bool
    {
        return $this->conflictBetweenPrinterClassAndTestdox;
    }
}
