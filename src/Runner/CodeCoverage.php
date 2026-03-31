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

use function assert;
use function implode;
use function sprintf;
use function sys_get_temp_dir;
use DateTimeImmutable;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Output\Printer;
use PHPUnit\Util\Filesystem;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Exception as CodeCoverageException;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Facade as ReportFacade;
use SebastianBergmann\CodeCoverage\Report\Html\Colors;
use SebastianBergmann\CodeCoverage\Report\Html\CustomCssFile;
use SebastianBergmann\CodeCoverage\Report\Thresholds;
use SebastianBergmann\CodeCoverage\Serialization\Serializer;
use SebastianBergmann\CodeCoverage\StaticAnalysis\CacheWarmer;
use SebastianBergmann\CodeCoverage\Test\Target\TargetCollection;
use SebastianBergmann\CodeCoverage\Test\Target\ValidationFailure;
use SebastianBergmann\CodeCoverage\Test\TestSize;
use SebastianBergmann\CodeCoverage\Test\TestStatus;
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
    private ?Driver $driver  = null;
    private bool $collecting = false;
    private ?TestCase $test  = null;
    private ?Timer $timer    = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function init(Configuration $configuration, CodeCoverageFilterRegistry $codeCoverageFilterRegistry, bool $extensionRequiresCodeCoverageCollection): CodeCoverageInitializationStatus
    {
        $codeCoverageFilterRegistry->init($configuration);

        if (!$configuration->hasCoverageReport() && !$extensionRequiresCodeCoverageCollection) {
            return CodeCoverageInitializationStatus::NOT_REQUESTED;
        }

        $this->activate($codeCoverageFilterRegistry->get(), $configuration->pathCoverage());

        if (!$this->isActive()) {
            return CodeCoverageInitializationStatus::FAILED;
        }

        if ($configuration->hasCoverageCacheDirectory()) {
            $coverageCacheDirectory = $configuration->coverageCacheDirectory();
        } else {
            $candidate = sys_get_temp_dir() . '/phpunit-code-coverage-cache';

            if (Filesystem::createDirectory($candidate)) {
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

        $this->warnIfFilterIsNotConfigured($codeCoverageFilterRegistry, $configuration);

        if (isset($coverageCacheDirectory) && $configuration->includeUncoveredFiles()) {
            EventFacade::emitter()->testRunnerStartedStaticAnalysisForCodeCoverage();

            /** @phpstan-ignore new.internalClass,method.internalClass */
            $statistics = (new CacheWarmer)->warmCache(
                $coverageCacheDirectory,
                !$configuration->disableCodeCoverageIgnore(),
                $configuration->ignoreDeprecatedCodeUnitsFromCodeCoverage(),
                $codeCoverageFilterRegistry->get(),
            );

            EventFacade::emitter()->testRunnerFinishedStaticAnalysisForCodeCoverage(
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
        return $this->codeCoverage;
    }

    /**
     * @return non-empty-string
     */
    public function driverNameAndVersion(): string
    {
        return $this->driver->nameAndVersion();
    }

    public function start(TestCase $test): void
    {
        if ($this->collecting) {
            return;
        }

        $size = TestSize::Unknown;

        if ($test->size()->isSmall()) {
            $size = TestSize::Small;
        } elseif ($test->size()->isMedium()) {
            $size = TestSize::Medium;
        } elseif ($test->size()->isLarge()) {
            $size = TestSize::Large;
        }

        $this->test = $test;

        $this->codeCoverage->start(
            $test->valueObjectForEvents()->id(),
            $size,
        );

        $this->collecting = true;

        $this->timer()->start();
    }

    public function stop(bool $append, null|false|TargetCollection $covers = null, ?TargetCollection $uses = null): void
    {
        if (!$this->collecting) {
            return;
        }

        $time             = $this->timer()->stop()->asSeconds();
        $status           = TestStatus::Unknown;
        $this->collecting = false;

        if ($this->test !== null) {
            if ($this->test->status()->isSuccess()) {
                $status = TestStatus::Success;
            } else {
                $status = TestStatus::Failure;
            }
        }

        if ($covers instanceof TargetCollection) {
            $result = $this->codeCoverage->validate($covers);

            if ($result->isFailure()) {
                assert($result instanceof ValidationFailure);

                EventFacade::emitter()->testTriggeredPhpunitWarning(
                    $this->test->valueObjectForEvents(),
                    $result->message(),
                );

                $append = false;
            }
        }

        if ($uses instanceof TargetCollection) {
            $result = $this->codeCoverage->validate($uses);

            if ($result->isFailure()) {
                assert($result instanceof ValidationFailure);

                EventFacade::emitter()->testTriggeredPhpunitWarning(
                    $this->test->valueObjectForEvents(),
                    $result->message(),
                );

                $append = false;
            }
        }

        $this->codeCoverage->stop($append, $status, $covers, $uses, $time);

        $this->test = null;
    }

    public function deactivate(): void
    {
        $this->driver       = null;
        $this->codeCoverage = null;
        $this->test         = null;
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
                $driverInformation = $this->codeCoverage->driverInformation();

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

    public function warnIfFilterIsNotConfigured(CodeCoverageFilterRegistry $codeCoverageFilterRegistry, Configuration $configuration): void
    {
        if (!$codeCoverageFilterRegistry->get()->isEmpty()) {
            return;
        }

        if (!$codeCoverageFilterRegistry->configured()) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
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

        EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
            sprintf(
                'Configured source filter (include-path: %s) does not match any files, code coverage will not be processed',
                implode(', ', $paths),
            ),
        );

        $this->deactivate();
    }

    private function activate(Filter $filter, bool $pathCoverage): void
    {
        try {
            if ($pathCoverage) {
                $this->driver = (new Selector)->forLineAndPathCoverage($filter);
            } else {
                $this->driver = (new Selector)->forLineCoverage($filter);
            }

            $this->codeCoverage = new \SebastianBergmann\CodeCoverage\CodeCoverage(
                $this->driver,
                $filter,
            );
        } catch (CodeCoverageException $e) {
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning(
                $e->getMessage(),
            );
        }
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
}
