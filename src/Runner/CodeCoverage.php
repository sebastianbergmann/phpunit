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

use function file_put_contents;
use function sprintf;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\TestData\MoreThanOneDataSetFromDataProviderException;
use PHPUnit\Event\TestData\NoDataSetFromDataProviderException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Output\Printer;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Exception as CodeCoverageException;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Clover as CloverReport;
use SebastianBergmann\CodeCoverage\Report\Cobertura as CoberturaReport;
use SebastianBergmann\CodeCoverage\Report\Crap4j as Crap4jReport;
use SebastianBergmann\CodeCoverage\Report\Html\Colors;
use SebastianBergmann\CodeCoverage\Report\Html\CustomCssFile;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;
use SebastianBergmann\CodeCoverage\Report\PHP as PhpReport;
use SebastianBergmann\CodeCoverage\Report\Text as TextReport;
use SebastianBergmann\CodeCoverage\Report\Thresholds;
use SebastianBergmann\CodeCoverage\Report\Xml\Facade as XmlReport;
use SebastianBergmann\CodeCoverage\Test\TestSize\TestSize;
use SebastianBergmann\CodeCoverage\Test\TestStatus\TestStatus;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Timer\NoActiveTimerException;
use SebastianBergmann\Timer\Timer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class CodeCoverage
{
    private static ?\SebastianBergmann\CodeCoverage\CodeCoverage $instance = null;
    private static ?Driver $driver                                         = null;
    private static bool $collecting                                        = false;
    private static ?TestCase $test                                         = null;
    private static ?Timer $timer                                           = null;

    public static function init(Configuration $configuration): void
    {
        CodeCoverageFilterRegistry::init($configuration);

        if (!$configuration->hasCoverageReport()) {
            return;
        }

        self::activate(CodeCoverageFilterRegistry::get(), $configuration->pathCoverage());

        if (!self::isActive()) {
            return;
        }

        if ($configuration->hasCoverageCacheDirectory()) {
            self::instance()->cacheStaticAnalysis($configuration->coverageCacheDirectory());
        }

        self::instance()->excludeSubclassesOfThisClassFromUnintentionallyCoveredCodeCheck(Comparator::class);

        if ($configuration->strictCoverage()) {
            self::instance()->enableCheckForUnintentionallyCoveredCode();
        }

        if ($configuration->ignoreDeprecatedCodeUnitsFromCodeCoverage()) {
            self::instance()->ignoreDeprecatedCode();
        } else {
            self::instance()->doNotIgnoreDeprecatedCode();
        }

        if ($configuration->disableCodeCoverageIgnore()) {
            self::instance()->disableAnnotationsForIgnoringCode();
        } else {
            self::instance()->enableAnnotationsForIgnoringCode();
        }

        if ($configuration->includeUncoveredFiles()) {
            self::instance()->includeUncoveredFiles();
        } else {
            self::instance()->excludeUncoveredFiles();
        }

        if (CodeCoverageFilterRegistry::get()->isEmpty()) {
            if (!CodeCoverageFilterRegistry::configured()) {
                EventFacade::emitter()->testRunnerTriggeredWarning(
                    'No filter is configured, code coverage will not be processed'
                );
            } else {
                EventFacade::emitter()->testRunnerTriggeredWarning(
                    'Incorrect filter configuration, code coverage will not be processed'
                );
            }

            self::deactivate();
        }
    }

    /**
     * @psalm-assert-if-true !null self::$instance
     */
    public static function isActive(): bool
    {
        return self::$instance !== null;
    }

    public static function instance(): \SebastianBergmann\CodeCoverage\CodeCoverage
    {
        return self::$instance;
    }

    public static function driver(): Driver
    {
        return self::$driver;
    }

    /**
     * @throws MoreThanOneDataSetFromDataProviderException
     * @throws NoDataSetFromDataProviderException
     */
    public static function start(TestCase $test): void
    {
        if (self::$collecting) {
            return;
        }

        $size = TestSize::unknown();

        if ($test->size()->isSmall()) {
            $size = TestSize::small();
        } elseif ($test->size()->isMedium()) {
            $size = TestSize::medium();
        } elseif ($test->size()->isLarge()) {
            $size = TestSize::large();
        }

        self::$test = $test;

        self::$instance->start(
            $test->valueObjectForEvents()->id(),
            $size
        );

        self::$collecting = true;
    }

    public static function stop(bool $append = true, array|false $linesToBeCovered = [], array $linesToBeUsed = []): void
    {
        if (!self::$collecting) {
            return;
        }

        $status = TestStatus::unknown();

        if (self::$test !== null) {
            if (self::$test->status()->isSuccess()) {
                $status = TestStatus::success();
            } else {
                $status = TestStatus::failure();
            }
        }

        /* @noinspection UnusedFunctionResultInspection */
        self::$instance->stop($append, $status, $linesToBeCovered, $linesToBeUsed);

        self::$test       = null;
        self::$collecting = false;
    }

    public static function deactivate(): void
    {
        self::$driver   = null;
        self::$instance = null;
        self::$test     = null;
    }

    public static function generateReports(Printer $printer, Configuration $configuration): void
    {
        if (!self::isActive()) {
            return;
        }

        if ($configuration->hasCoverageClover()) {
            self::codeCoverageGenerationStart($printer, 'Clover XML');

            try {
                $writer = new CloverReport;
                $writer->process(self::instance(), $configuration->coverageClover());

                self::codeCoverageGenerationSucceeded($printer);

                unset($writer);
            } catch (CodeCoverageException $e) {
                self::codeCoverageGenerationFailed($printer, $e);
            }
        }

        if ($configuration->hasCoverageCobertura()) {
            self::codeCoverageGenerationStart($printer, 'Cobertura XML');

            try {
                $writer = new CoberturaReport;
                $writer->process(self::instance(), $configuration->coverageCobertura());

                self::codeCoverageGenerationSucceeded($printer);

                unset($writer);
            } catch (CodeCoverageException $e) {
                self::codeCoverageGenerationFailed($printer, $e);
            }
        }

        if ($configuration->hasCoverageCrap4j()) {
            self::codeCoverageGenerationStart($printer, 'Crap4J XML');

            try {
                $writer = new Crap4jReport($configuration->coverageCrap4jThreshold());
                $writer->process(self::instance(), $configuration->coverageCrap4j());

                self::codeCoverageGenerationSucceeded($printer);

                unset($writer);
            } catch (CodeCoverageException $e) {
                self::codeCoverageGenerationFailed($printer, $e);
            }
        }

        if ($configuration->hasCoverageHtml()) {
            self::codeCoverageGenerationStart($printer, 'HTML');

            try {
                $customCssFile = CustomCssFile::default();

                if ($configuration->hasCoverageHtmlCustomCssFile()) {
                    $customCssFile = CustomCssFile::from($configuration->coverageHtmlCustomCssFile());
                }

                $writer = new HtmlReport(
                    sprintf(
                        ' and <a href="https://phpunit.de/">PHPUnit %s</a>',
                        Version::id()
                    ),
                    Colors::from(
                        $configuration->coverageHtmlColorSuccessLow(),
                        $configuration->coverageHtmlColorSuccessMedium(),
                        $configuration->coverageHtmlColorSuccessHigh(),
                        $configuration->coverageHtmlColorWarning(),
                        $configuration->coverageHtmlColorDanger(),
                    ),
                    Thresholds::from(
                        $configuration->coverageHtmlLowUpperBound(),
                        $configuration->coverageHtmlHighLowerBound()
                    ),
                    $customCssFile
                );

                $writer->process(self::instance(), $configuration->coverageHtml());

                self::codeCoverageGenerationSucceeded($printer);

                unset($writer);
            } catch (CodeCoverageException $e) {
                self::codeCoverageGenerationFailed($printer, $e);
            }
        }

        if ($configuration->hasCoveragePhp()) {
            self::codeCoverageGenerationStart($printer, 'PHP');

            try {
                $writer = new PhpReport;
                $writer->process(self::instance(), $configuration->coveragePhp());

                self::codeCoverageGenerationSucceeded($printer);

                unset($writer);
            } catch (CodeCoverageException $e) {
                self::codeCoverageGenerationFailed($printer, $e);
            }
        }

        if ($configuration->hasCoverageText()) {
            $processor = new TextReport(
                Thresholds::default(),
                $configuration->coverageTextShowUncoveredFiles(),
                $configuration->coverageTextShowOnlySummary()
            );

            $textReport = $processor->process(self::instance(), $configuration->colors());

            if ($configuration->coverageText() === 'php://stdout') {
                $printer->print($textReport);
            } else {
                file_put_contents($configuration->coverageText(), $textReport);
            }
        }

        if ($configuration->hasCoverageXml()) {
            self::codeCoverageGenerationStart($printer, 'PHPUnit XML');

            try {
                $writer = new XmlReport(Version::id());
                $writer->process(self::instance(), $configuration->coverageXml());

                self::codeCoverageGenerationSucceeded($printer);

                unset($writer);
            } catch (CodeCoverageException $e) {
                self::codeCoverageGenerationFailed($printer, $e);
            }
        }
    }

    private static function activate(Filter $filter, bool $pathCoverage): void
    {
        try {
            if ($pathCoverage) {
                self::$driver = (new Selector)->forLineAndPathCoverage($filter);
            } else {
                self::$driver = (new Selector)->forLineCoverage($filter);
            }

            self::$instance = new \SebastianBergmann\CodeCoverage\CodeCoverage(
                self::$driver,
                $filter
            );
        } catch (CodeCoverageException $e) {
            EventFacade::emitter()->testRunnerTriggeredWarning(
                $e->getMessage()
            );
        }
    }

    private static function codeCoverageGenerationStart(Printer $printer, string $format): void
    {
        $printer->print(
            sprintf(
                "\nGenerating code coverage report in %s format ... ",
                $format
            )
        );

        self::timer()->start();
    }

    /**
     * @throws NoActiveTimerException
     */
    private static function codeCoverageGenerationSucceeded(Printer $printer): void
    {
        $printer->print(
            sprintf(
                "done [%s]\n",
                self::timer()->stop()->asString()
            )
        );
    }

    /**
     * @throws NoActiveTimerException
     */
    private static function codeCoverageGenerationFailed(Printer $printer, CodeCoverageException $e): void
    {
        $printer->print(
            sprintf(
                "failed [%s]\n%s\n",
                self::timer()->stop()->asString(),
                $e->getMessage()
            )
        );
    }

    private static function timer(): Timer
    {
        if (self::$timer === null) {
            self::$timer = new Timer;
        }

        return self::$timer;
    }
}
