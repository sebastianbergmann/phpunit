<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use const PHP_OS_FAMILY;
use function assert;
use function class_exists;
use function implode;
use function is_subclass_of;
use function sprintf;
use function sys_get_temp_dir;
use DateTimeImmutable;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Output\Printer;
use PHPUnit\Util\Filesystem;
use ReflectionClass;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Driver\Granularity;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Exception as CodeCoverageException;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Facade as ReportFacade;
use SebastianBergmann\CodeCoverage\Report\Html\Colors;
use SebastianBergmann\CodeCoverage\Report\Html\CustomCssFile;
use SebastianBergmann\CodeCoverage\Report\Html\Views;
use SebastianBergmann\CodeCoverage\Report\Thresholds;
use SebastianBergmann\CodeCoverage\Serialization\Serializer;
use SebastianBergmann\CodeCoverage\StaticAnalysis\CacheWarmer;
use SebastianBergmann\CodeCoverage\Test\Target\TargetCollection;
use SebastianBergmann\CodeCoverage\Test\Target\ValidationFailure;
use SebastianBergmann\CodeCoverage\Test\TestSize;
use SebastianBergmann\CodeCoverage\Test\TestStatus;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\CodeCoverage\Version as CodeCoverageVersion;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Environment\Runtime;
use SebastianBergmann\Timer\NoActiveTimerException;
use SebastianBergmann\Timer\Timer;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @codeCoverageIgnore
 */
final class CodeCoverage
{
    private static ?self $instance                                      = null;
    private ?\SebastianBergmann\CodeCoverage\CodeCoverage $codeCoverage = null;

    /**
     * @phpstan-ignore property.internalClass
     */
    private ?Driver $driver                     = null;
    private bool $collecting                    = false;
    private ?TestCase $test                     = null;
    private ?Timer $timer                       = null;
    private bool $requireCoverageContribution   = false;
    private bool $lastTestContributedToCoverage = false;

    /**
     * The code coverage data of the examples of a property that passed
     * without performing assertions, by the ID of the property: whether it is
     * appended depends on whether the property as a whole performed
     * assertions, which is only known once all of its examples were checked.
     * Only what is needed to append the data is kept of an example, not the
     * example itself, which would keep its arguments, test doubles, and
     * fixture alive until the property is decided.
     *
     * @var array<string, list<array{test: Test, size: TestSize, data: RawCodeCoverageData, time: float, covers: null|false|TargetCollection, uses: ?TargetCollection}>>
     *
     * @phpstan-ignore property.internalClass
     */
    private array $heldBack              = [];
    private bool $collectsBranchCoverage = false;
    private bool $collectsPathCoverage   = false;
    private readonly Emitter $emitter;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self(EventFacade::emitter());
        }

        return self::$instance;
    }

    /**
     * @return non-empty-string
     */
    public static function unintentionallyCoveredCodeMessage(UnintentionallyCoveredCodeException $e): string
    {
        return "This test executed code that is not listed as code to be covered or used:\n" .
               $e->getMessage();
    }

    public function __construct(Emitter $emitter)
    {
        $this->emitter = $emitter;
    }

    public function init(Configuration $configuration, CodeCoverageFilterRegistry $codeCoverageFilterRegistry, bool $extensionRequiresCodeCoverageCollection): CodeCoverageInitializationStatus
    {
        $codeCoverageFilterRegistry->init($configuration, $extensionRequiresCodeCoverageCollection);

        if (!$configuration->hasCoverageReport() && !$extensionRequiresCodeCoverageCollection) {
            return CodeCoverageInitializationStatus::NOT_REQUESTED;
        }

        $coverageDriver = null;

        if ($configuration->hasCoverageDriver()) {
            $coverageDriver = $configuration->coverageDriver();
        }

        $this->activate(
            $codeCoverageFilterRegistry->get(),
            $configuration->branchCoverage(),
            $configuration->pathCoverage(),
            $coverageDriver,
        );

        if (!$this->isActive()) {
            return CodeCoverageInitializationStatus::FAILED;
        }

        if ($configuration->hasCoverageCacheDirectory()) {
            $coverageCacheDirectory = $configuration->coverageCacheDirectory();
        } else {
            $candidate = $this->temporaryCoverageCacheDirectory();

            if ($candidate !== false) {
                $coverageCacheDirectory = $candidate;
            }
        }

        if (isset($coverageCacheDirectory)) {
            $this->codeCoverage()->cacheStaticAnalysis($coverageCacheDirectory);
        }

        $this->codeCoverage()->excludeSubclassesOfThisClassFromUnintentionallyCoveredCodeCheck(Comparator::class);

        if ($configuration->strictCoverage()) {
            $this->codeCoverage()->enableCheckForUnintentionallyCoveredCode();
        }

        if ($configuration->ignoreDeprecatedCodeUnitsFromCodeCoverage()) {
            $this->codeCoverage()->ignoreDeprecatedCode();
        } else {
            $this->codeCoverage()->doNotIgnoreDeprecatedCode();
        }

        if ($configuration->disableCodeCoverageIgnore()) {
            $this->codeCoverage()->disableAnnotationsForIgnoringCode();
        } else {
            $this->codeCoverage()->enableAnnotationsForIgnoringCode();
        }

        if ($configuration->includeUncoveredFiles()) {
            $this->codeCoverage()->includeUncoveredFiles();
        } else {
            $this->codeCoverage()->excludeUncoveredFiles();
        }

        $this->requireCoverageContribution = $configuration->requireCoverageContribution();

        $this->warnIfFilterIsNotConfigured($codeCoverageFilterRegistry, $configuration);

        if (isset($coverageCacheDirectory) && $configuration->includeUncoveredFiles()) {
            $this->emitter->testRunnerStartedStaticAnalysisForCodeCoverage();

            /** @phpstan-ignore new.internalClass,method.internalClass */
            $statistics = (new CacheWarmer)->warmCache(
                $coverageCacheDirectory,
                !$configuration->disableCodeCoverageIgnore(),
                $configuration->ignoreDeprecatedCodeUnitsFromCodeCoverage(),
                $codeCoverageFilterRegistry->get(),
            );

            $this->emitter->testRunnerFinishedStaticAnalysisForCodeCoverage(
                $statistics['cacheHits'],
                $statistics['cacheMisses'],
            );
        }

        return CodeCoverageInitializationStatus::SUCCEEDED;
    }

    /**
     * @phpstan-assert-if-true !null $this->codeCoverage
     */
    public function isActive(): bool
    {
        return $this->codeCoverage !== null;
    }

    public function codeCoverage(): \SebastianBergmann\CodeCoverage\CodeCoverage
    {
        assert($this->codeCoverage !== null);

        return $this->codeCoverage;
    }

    /**
     * @return non-empty-string
     */
    public function driverNameAndVersion(): string
    {
        assert($this->driver !== null);

        /** @phpstan-ignore method.internalClass */
        $nameAndVersion = $this->driver->nameAndVersion();

        if ($nameAndVersion === '') {
            return 'unknown';
        }

        return $nameAndVersion;
    }

    public function start(TestCase $test): void
    {
        if ($this->collecting) {
            return;
        }

        $this->test = $test;

        assert($this->codeCoverage !== null);

        $this->codeCoverage->start(
            $test->valueObjectForEvents()->id(),
            $this->sizeOf($test),
        );

        $this->collecting = true;

        $this->timer()->start();
    }

    public function stop(bool $append, null|false|TargetCollection $covers = null, ?TargetCollection $uses = null): void
    {
        if (!$this->collecting) {
            return;
        }

        assert($this->codeCoverage !== null);
        assert($this->test !== null);

        $time             = $this->timer()->stop()->asSeconds();
        $this->collecting = false;

        if ($this->test->status()->isSuccess()) {
            $status = TestStatus::Success;
        } else {
            $status = TestStatus::Failure;
        }

        if (!$this->targetsAreValid($this->test->valueObjectForEvents(), $covers, $uses)) {
            $append = false;
        }

        $rawData = $this->codeCoverage->stop($append, $status, $covers, $uses, $time);

        if ($this->requireCoverageContribution) {
            $this->lastTestContributedToCoverage = $this->contributes($rawData);
        }

        $this->test = null;
    }

    /**
     * Stops collecting code coverage data for the current test, an example of
     * a property that passed without performing assertions, and holds the
     * data back instead of appending it, see appendHeldBack().
     */
    public function stopAndHoldBack(null|false|TargetCollection $covers, ?TargetCollection $uses): void
    {
        if (!$this->collecting) {
            return;
        }

        assert($this->codeCoverage !== null);
        assert($this->test !== null);

        $time             = $this->timer()->stop()->asSeconds();
        $this->collecting = false;

        $test = $this->test->valueObjectForEvents();

        $this->heldBack[$test->id()][] = [
            'test'   => $test,
            'size'   => $this->sizeOf($this->test),
            'data'   => $this->codeCoverage->stop(false),
            'time'   => $time,
            'covers' => $covers,
            'uses'   => $uses,
        ];

        $this->test = null;
    }

    /**
     * Appends the code coverage data that was held back for the examples of a
     * property, and returns whether it contributed to code coverage, or null
     * when that is not checked because contribution to code coverage is not
     * required or no data was appended.
     *
     * The size, status, and time that are recorded for the property are not
     * changed by this, they are those of the examples that were appended
     * before.
     *
     * @param non-empty-string $id
     */
    public function appendHeldBack(string $id): ?bool
    {
        assert($this->codeCoverage !== null);

        $heldBack    = $this->heldBack[$id] ?? [];
        $contributed = null;

        unset($this->heldBack[$id]);

        foreach ($heldBack as $example) {
            $test = $example['test'];

            if (!$this->targetsAreValid($test, $example['covers'], $example['uses'])) {
                continue;
            }

            $tests = $this->codeCoverage->getTests();

            // php-code-coverage only learns the size of a test, which matters
            // for the check for unintentionally covered code, when it starts
            // collecting code coverage data for it, so it does that, without
            // appending the data that is collected meanwhile
            $this->codeCoverage->start($id, $example['size']);

            try {
                $this->codeCoverage->append($example['data'], $id, true, TestStatus::Success, $example['covers'], $example['uses'], $example['time']);
            } catch (UnintentionallyCoveredCodeException $e) {
                $this->emitter->testConsideredRisky(
                    $test,
                    self::unintentionallyCoveredCodeMessage($e),
                );

                continue;
            } catch (CodeCoverageException) {
                continue;
            } finally {
                $this->codeCoverage->stop(false);

                if (isset($tests[$id])) {
                    $this->codeCoverage->setTests($tests);
                }
            }

            if ($this->requireCoverageContribution && $contributed !== true) {
                $contributed = $this->contributes($example['data']);
            }
        }

        return $contributed;
    }

    /**
     * Discards the code coverage data that was held back for the examples of a
     * property, like the data of a test that is considered risky because it
     * did not perform any assertions.
     */
    public function discardHeldBack(string $id): void
    {
        unset($this->heldBack[$id]);
    }

    public function lastTestContributedToCoverage(): bool
    {
        return $this->lastTestContributedToCoverage;
    }

    public function deactivate(): void
    {
        $this->driver                 = null;
        $this->codeCoverage           = null;
        $this->test                   = null;
        $this->collectsBranchCoverage = false;
        $this->collectsPathCoverage   = false;
    }

    public function collectsBranchCoverage(): bool
    {
        return $this->collectsBranchCoverage;
    }

    public function collectsPathCoverage(): bool
    {
        return $this->collectsPathCoverage;
    }

    public function generateReports(Printer $printer, Configuration $configuration): void
    {
        if (!$this->isActive()) {
            return;
        }

        if ($configuration->hasCoveragePhp()) {
            $this->codeCoverageGenerationStart($printer, 'PHP');

            $serializer = new Serializer;

            $serializer->serialize($configuration->coveragePhp(), $this->codeCoverage(), $configuration->includeGitInformation());

            $this->codeCoverageGenerationSucceeded($printer);

            unset($serializer);
        }

        $facade = ReportFacade::fromObject($this->codeCoverage());

        if ($configuration->hasCoverageClover()) {
            $this->codeCoverageGenerationStart($printer, 'Clover XML');

            try {
                $facade->renderClover($configuration->coverageClover(), 'Clover Coverage');

                $this->codeCoverageGenerationSucceeded($printer);
            } catch (CodeCoverageException $e) {
                $this->codeCoverageGenerationFailed($printer, $e);
            }
        }

        if ($configuration->hasCoverageJsonl()) {
            $this->codeCoverageGenerationStart($printer, 'JSONL');

            try {
                $facade->renderJsonl($configuration->coverageJsonl());

                $this->codeCoverageGenerationSucceeded($printer);
            } catch (CodeCoverageException $e) {
                $this->codeCoverageGenerationFailed($printer, $e);
            }
        }

        if ($configuration->hasCoverageOpenClover()) {
            $this->codeCoverageGenerationStart($printer, 'OpenClover XML');

            try {
                $facade->renderOpenClover($configuration->coverageOpenClover(), 'OpenClover Coverage');

                $this->codeCoverageGenerationSucceeded($printer);
            } catch (CodeCoverageException $e) {
                $this->codeCoverageGenerationFailed($printer, $e);
            }
        }

        if ($configuration->hasCoverageCobertura()) {
            $this->codeCoverageGenerationStart($printer, 'Cobertura XML');

            try {
                $facade->renderCobertura($configuration->coverageCobertura());

                $this->codeCoverageGenerationSucceeded($printer);
            } catch (CodeCoverageException $e) {
                $this->codeCoverageGenerationFailed($printer, $e);
            }
        }

        if ($configuration->hasCoverageCrap4j()) {
            $this->codeCoverageGenerationStart($printer, 'Crap4J XML');

            try {
                $facade->renderCrap4j($configuration->coverageCrap4j(), $configuration->coverageCrap4jThreshold());

                $this->codeCoverageGenerationSucceeded($printer);
            } catch (CodeCoverageException $e) {
                $this->codeCoverageGenerationFailed($printer, $e);
            }
        }

        if ($configuration->hasCoverageHtml()) {
            $this->codeCoverageGenerationStart($printer, 'HTML');

            try {
                $customCssFile = CustomCssFile::default();

                if ($configuration->hasCoverageHtmlCustomCssFile()) {
                    $customCssFile = CustomCssFile::from($configuration->coverageHtmlCustomCssFile());
                }

                if ($configuration->coverageHtmlClassView() && $configuration->coverageHtmlFileView()) {
                    $views = Views::FileViewAndClassView;
                } elseif ($configuration->coverageHtmlClassView()) {
                    $views = Views::OnlyClassView;
                } else {
                    $views = Views::OnlyFileView;
                }

                $facade->renderHtml(
                    $configuration->coverageHtml(),
                    sprintf(
                        ' and <a href="https://phpunit.de/">PHPUnit %s</a>',
                        Version::id(),
                    ),
                    Colors::from(
                        $configuration->coverageHtmlColorSuccessLow(),
                        $configuration->coverageHtmlColorSuccessLowDark(),
                        $configuration->coverageHtmlColorSuccessMedium(),
                        $configuration->coverageHtmlColorSuccessMediumDark(),
                        $configuration->coverageHtmlColorSuccessHigh(),
                        $configuration->coverageHtmlColorSuccessHighDark(),
                        $configuration->coverageHtmlColorSuccessBar(),
                        $configuration->coverageHtmlColorSuccessBarDark(),
                        $configuration->coverageHtmlColorWarning(),
                        $configuration->coverageHtmlColorWarningDark(),
                        $configuration->coverageHtmlColorWarningBar(),
                        $configuration->coverageHtmlColorWarningBarDark(),
                        $configuration->coverageHtmlColorDanger(),
                        $configuration->coverageHtmlColorDangerDark(),
                        $configuration->coverageHtmlColorDangerBar(),
                        $configuration->coverageHtmlColorDangerBarDark(),
                        $configuration->coverageHtmlColorBreadcrumbs(),
                        $configuration->coverageHtmlColorBreadcrumbsDark(),
                    ),
                    Thresholds::from(
                        $configuration->coverageHtmlLowUpperBound(),
                        $configuration->coverageHtmlHighLowerBound(),
                    ),
                    $customCssFile,
                    $views,
                );

                $this->codeCoverageGenerationSucceeded($printer);
            } catch (CodeCoverageException $e) {
                $this->codeCoverageGenerationFailed($printer, $e);
            }
        }

        if ($configuration->hasCoverageText()) {
            if ($configuration->coverageText() === 'php://stdout') {
                if (!$configuration->noOutput() && !$configuration->debug()) {
                    $printer->print(
                        $facade->renderText(
                            null,
                            Thresholds::default(),
                            $configuration->coverageTextShowUncoveredFiles(),
                            $configuration->coverageTextShowOnlySummary(),
                            $configuration->colors(),
                        ),
                    );
                }
            } else {
                $facade->renderText(
                    $configuration->coverageText(),
                    Thresholds::default(),
                    $configuration->coverageTextShowUncoveredFiles(),
                    $configuration->coverageTextShowOnlySummary(),
                    $configuration->colors(),
                );
            }
        }

        if ($configuration->hasCoverageXml()) {
            $this->codeCoverageGenerationStart($printer, 'PHPUnit XML');

            try {
                /** @phpstan-ignore method.internal */
                $driverInformation = $this->codeCoverage()->driverInformation();

                $facade->renderXml(
                    $configuration->coverageXml(),
                    $configuration->coverageXmlIncludeSource(),
                    new Runtime,
                    new DateTimeImmutable,
                    Version::id(),
                    CodeCoverageVersion::id(),
                    $driverInformation['name'],
                    $driverInformation['version'],
                );

                $this->codeCoverageGenerationSucceeded($printer);
            } catch (CodeCoverageException $e) {
                $this->codeCoverageGenerationFailed($printer, $e);
            }
        }
    }

    public function warnAboutFilesThatCouldNotBeParsed(): void
    {
        if (!$this->isActive()) {
            return;
        }

        foreach ($this->codeCoverage->parseErrors() as $file => $message) {
            $this->emitter->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    'Cannot parse %s (%s), code coverage for this file is based on raw data reported by the code coverage driver',
                    $file,
                    $message,
                ),
            );
        }
    }

    public function warnIfFilterIsNotConfigured(CodeCoverageFilterRegistry $codeCoverageFilterRegistry, Configuration $configuration): void
    {
        if (!$codeCoverageFilterRegistry->get()->isEmpty()) {
            return;
        }

        if (!$codeCoverageFilterRegistry->configured()) {
            $this->emitter->testRunnerTriggeredPhpunitWarning(
                'No filter is configured, code coverage will not be processed',
            );

            $this->deactivate();

            return;
        }

        $paths = [];

        foreach ($configuration->source()->includeDirectories() as $directory) {
            $paths[] = $directory->path();
        }

        foreach ($configuration->source()->includeFiles() as $file) {
            $paths[] = $file->path();
        }

        $this->emitter->testRunnerTriggeredPhpunitWarning(
            sprintf(
                'Configured source filter (include-path: %s) does not match any files, code coverage will not be processed',
                implode(', ', $paths),
            ),
        );

        $this->deactivate();
    }

    private function activate(Filter $filter, bool $branchCoverage, bool $pathCoverage, ?string $driverClass = null): void
    {
        try {
            $granularity = Granularity::Line;

            if ($branchCoverage) {
                $granularity = Granularity::LineAndBranch;
            }

            if ($pathCoverage) {
                $branchCoverage = true;
                $granularity    = Granularity::LineBranchAndPath;
            }

            if ($driverClass !== null) {
                $this->driver = $this->instantiateDriver($driverClass, $filter, $granularity);
            } else {
                $this->driver = (new Selector)->select($filter, $granularity);
            }

            $this->codeCoverage = new \SebastianBergmann\CodeCoverage\CodeCoverage(
                $this->driver,
                $filter,
            );

            $this->collectsBranchCoverage = $branchCoverage;
            $this->collectsPathCoverage   = $pathCoverage;
        } catch (CodeCoverageException $e) {
            $message = $e->getMessage();

            if ($message === '') {
                $message = 'Code coverage cannot be initialized';
            }

            $this->emitter->testRunnerTriggeredPhpunitWarning($message);
        }
    }

    /**
     * @phpstan-ignore return.internalClass
     */
    private function instantiateDriver(string $driverClass, Filter $filter, Granularity $granularity): Driver
    {
        if (!class_exists($driverClass)) {
            throw new CodeCoverageDriverException(
                sprintf(
                    'Configured code coverage driver class "%s" does not exist',
                    $driverClass,
                ),
            );
        }

        /** @phpstan-ignore classConstant.internalClass */
        if (!is_subclass_of($driverClass, Driver::class)) {
            throw new CodeCoverageDriverException(
                sprintf(
                    'Configured code coverage driver class "%s" does not extend %s',
                    $driverClass,
                    /** @phpstan-ignore classConstant.internalClass */
                    Driver::class,
                ),
            );
        }

        $reflection = new ReflectionClass($driverClass);

        if (!$reflection->isInstantiable()) {
            throw new CodeCoverageDriverException(
                sprintf(
                    'Configured code coverage driver class "%s" is not instantiable',
                    $driverClass,
                ),
            );
        }

        $constructor = $reflection->getConstructor();

        if ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) {
            $driver = $reflection->newInstance($filter);
        } else {
            $driver = $reflection->newInstance();
        }

        /** @phpstan-ignore instanceof.internalClass */
        assert($driver instanceof Driver);

        /** @phpstan-ignore method.internalClass */
        $driver->setGranularity($granularity);

        return $driver;
    }

    /**
     * The directory below the operating system's path used for temporary
     * files that is used for caching information from static analysis when
     * no cache directory is configured.
     *
     * The path used for temporary files can be shared between users, for
     * instance /tmp on Linux. Another user must not be able to control the
     * cached information, so the directory is specific to the current user
     * and is only used when no other user can write to it.
     *
     * @return false|non-empty-string
     */
    private function temporaryCoverageCacheDirectory(): false|string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // By default, the path used for temporary files is specific to
            // the current user on Windows
            $directory = sys_get_temp_dir() . '/phpunit-code-coverage-cache';

            if (Filesystem::createDirectory($directory)) {
                return $directory;
            }

            return false;
        }

        $userId = Filesystem::currentUserId();

        if ($userId === false) {
            return false;
        }

        $directory = sys_get_temp_dir() . '/phpunit-code-coverage-cache-' . $userId;

        if (Filesystem::createPrivateDirectory($directory, $userId)) {
            return $directory;
        }

        return false;
    }

    private function codeCoverageGenerationStart(Printer $printer, string $format): void
    {
        $printer->print(
            sprintf(
                "\nGenerating code coverage report in %s format ... ",
                $format,
            ),
        );

        $this->timer()->start();
    }

    /**
     * @throws NoActiveTimerException
     */
    private function codeCoverageGenerationSucceeded(Printer $printer): void
    {
        $printer->print(
            sprintf(
                "done [%s]\n",
                $this->timer()->stop()->asString(),
            ),
        );
    }

    /**
     * @throws NoActiveTimerException
     */
    private function codeCoverageGenerationFailed(Printer $printer, CodeCoverageException $e): void
    {
        $printer->print(
            sprintf(
                "failed [%s]\n%s\n",
                $this->timer()->stop()->asString(),
                $e->getMessage(),
            ),
        );
    }

    private function timer(): Timer
    {
        if ($this->timer === null) {
            $this->timer = new Timer;
        }

        return $this->timer;
    }

    private function sizeOf(TestCase $test): TestSize
    {
        if ($test->size()->isSmall()) {
            return TestSize::Small;
        }

        if ($test->size()->isMedium()) {
            return TestSize::Medium;
        }

        if ($test->size()->isLarge()) {
            return TestSize::Large;
        }

        return TestSize::Unknown;
    }

    /**
     * Emits a warning for each of the targets that are invalid.
     */
    private function targetsAreValid(Test $test, null|false|TargetCollection $covers, ?TargetCollection $uses): bool
    {
        assert($this->codeCoverage !== null);

        $valid = true;

        foreach ([$covers, $uses] as $targets) {
            if (!$targets instanceof TargetCollection) {
                continue;
            }

            $result = $this->codeCoverage->validate($targets);

            if ($result->isFailure()) {
                assert($result instanceof ValidationFailure);

                $this->emitter->testTriggeredPhpunitWarning(
                    $test,
                    $result->message(),
                );

                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * @phpstan-ignore parameter.internalClass
     */
    private function contributes(RawCodeCoverageData $data): bool
    {
        /** @phpstan-ignore method.internalClass */
        foreach ($data->lineCoverage() as $lines) {
            foreach ($lines as $lineStatus) {
                /** @phpstan-ignore classConstant.internalClass */
                if ($lineStatus === Driver::LINE_EXECUTED) {
                    return true;
                }
            }
        }

        return false;
    }
}
