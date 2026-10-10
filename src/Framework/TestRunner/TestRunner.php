<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestRunner;

use const DIRECTORY_SEPARATOR;
use function array_diff_assoc;
use function array_intersect;
use function array_unique;
use function extension_loaded;
use function realpath;
use function rtrim;
use function sprintf;
use function str_starts_with;
use function xdebug_is_debugger_active;
use AssertionError;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\ProcessIsolationException;
use PHPUnit\Framework\SkippedTest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Api\CodeCoverage as CodeCoverageMetadataApi;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\Runner\Exception;
use PHPUnit\Runner\ShutdownHandler;
use PHPUnit\Runner\TimeLimit\TimeLimitHandler;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\TextUI\Configuration\SourceFilter;
use SebastianBergmann\CodeCoverage\Exception as CodeCoverageException;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
use SebastianBergmann\CodeCoverage\Test\Target\Target;
use SebastianBergmann\CodeCoverage\Test\Target\TargetCollection;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\Invoker\Invoker;
use SebastianBergmann\Invoker\TimeoutException;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestRunner
{
    private readonly Configuration $configuration;
    private readonly Emitter $emitter;
    private readonly RiskyTestChecks $riskyTestChecks;

    /**
     * Whether PHPUnit's error handler handles the issues that are triggered
     * by the test, which it does unless #[WithoutErrorHandler] is used.
     */
    public static function shouldErrorHandlerBeUsed(TestCase $test): bool
    {
        if (MetadataRegistry::parser()->forMethod($test::class, $test->name())->isWithoutErrorHandler()->isNotEmpty()) {
            return false;
        }

        return true;
    }

    public function __construct(Emitter $emitter)
    {
        $this->configuration   = ConfigurationRegistry::get();
        $this->emitter         = $emitter;
        $this->riskyTestChecks = new RiskyTestChecks($this->configuration->reportUselessTests());
    }

    /**
     * @param bool $collectCodeCoverage whether code coverage is collected for the test, if it is collected at all: it is not for the shrinking attempts and the final replay of a property, for instance
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Util\Exception
     * @throws \SebastianBergmann\Template\InvalidArgumentException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws ProcessIsolationException
     * @throws UnintentionallyCoveredCodeException
     */
    public function run(TestCase $test, bool $collectCodeCoverage = true): void
    {
        if (new ProcessIsolation($this->emitter)->shouldBeUsedFor($test)) {
            new SeparateProcessTestRunner($this->emitter)->run($test);

            return;
        }

        try {
            ShutdownHandler::setMessage(sprintf('Fatal error: Premature end of PHP process when running %s.', $test->toString()));

            $this->runInProcess($test, $collectCodeCoverage);
        } finally {
            ShutdownHandler::resetMessage();
        }
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws UnintentionallyCoveredCodeException
     */
    private function runInProcess(TestCase $test, bool $collectCodeCoverageForTest): void
    {
        Assert::resetCount();

        $codeCoverageMetadataApi       = new CodeCoverageMetadataApi;
        $coversTargets                 = TargetCollection::fromArray([]);
        $usesTargets                   = TargetCollection::fromArray([]);
        $shouldCodeCoverageBeCollected = false;

        // the code coverage metadata of a test for which no code coverage data
        // is collected is not looked at: this is an example of a property
        // that is run while the property is shrunk, or for the final replay of
        // its minimal counterexample, and the examples of the property that
        // were checked before report the issues with this metadata
        if ($collectCodeCoverageForTest) {
            $coversNothingContradiction = false;

            if ($this->configuration->disableCoverageTargeting()) {
                $shouldCodeCoverageBeCollected = true;
            } else {
                $coversTargets = $codeCoverageMetadataApi->coversTargets(
                    $test::class,
                    $test->name(),
                );

                $usesTargets = $codeCoverageMetadataApi->usesTargets(
                    $test::class,
                    $test->name(),
                );

                $coversTargets = $this->removeFilesystemTargetsThatAreNotFirstPartyCode($test, $coversTargets);
                $usesTargets   = $this->removeFilesystemTargetsThatAreNotFirstPartyCode($test, $usesTargets);

                $shouldCodeCoverageBeCollected = $codeCoverageMetadataApi->shouldCodeCoverageBeCollectedFor($test);

                $coversNothingContradiction = $codeCoverageMetadataApi->coversNothingContradictsCoversOrUses(
                    $test::class,
                );
            }

            $this->performSanityChecks($test, $coversTargets, $usesTargets, $coversNothingContradiction);
        }

        $error      = false;
        $failure    = false;
        $incomplete = false;
        $risky      = false;
        $skipped    = false;

        if (self::shouldErrorHandlerBeUsed($test)) {
            $throwableFromDeferredIssue = ErrorHandler::instance()->enable($test);

            if ($throwableFromDeferredIssue !== null) {
                $test->setThrowableFromDeferredIssue($throwableFromDeferredIssue);
            }
        }

        $collectCodeCoverage = CodeCoverage::instance()->isActive() &&
                               $shouldCodeCoverageBeCollected &&
                               $collectCodeCoverageForTest;

        if ($collectCodeCoverage) {
            CodeCoverage::instance()->start($test);
        }

        try {
            TimeLimitHandler::armAlarm();

            if ($this->canTimeLimitBeEnforced() &&
                $this->shouldTimeLimitBeEnforced($test)) {
                $risky = $this->runTestWithTimeout($test);
            } else {
                $test->runLifecycle();
            }
        } catch (AssertionFailedError $e) {
            $failure = true;

            if ($e instanceof IncompleteTestError) {
                $incomplete = true;
            } elseif ($e instanceof SkippedTest) {
                $skipped = true;
            }
        } catch (AssertionError) {
            $test->addToAssertionCount(1);

            $failure = true;
        } catch (Throwable) {
            $error = true;
        } finally {
            TimeLimitHandler::disarmAlarm();
        }

        $test->addToAssertionCount(Assert::getCount());

        $riskyBecauseOfNoAssertions = $this->riskyTestChecks->performedNoAssertions(
            $test->numberOfAssertionsPerformed(),
            $test->doesNotPerformAssertions(),
        );

        // the code coverage data of an example of a property that passed
        // without performing assertions is held back, because whether the
        // property performed assertions, and therefore whether the data is
        // appended, is decided on all of its examples; it is not held back
        // when the example would be considered risky for lacking code
        // coverage metadata had it performed assertions
        $holdBackCodeCoverage = $collectCodeCoverage &&
                                $riskyBecauseOfNoAssertions !== null &&
                                $test->isPropertyExample() &&
                                $test->status()->isSuccess() &&
                                !$this->lacksRequiredCoverageMetadata($test);

        if ($riskyBecauseOfNoAssertions !== null) {
            $risky = true;
        }

        if (!$error && !$failure && !$incomplete && !$skipped && !$risky &&
            $this->lacksRequiredCoverageMetadata($test)) {
            $this->emitter->testConsideredRisky(
                $test->valueObjectForEvents(),
                'This test does not define a code coverage target but is expected to do so',
            );

            $risky = true;
        }

        if ($holdBackCodeCoverage) {
            CodeCoverage::instance()->stopAndHoldBack($coversTargets, $usesTargets);
        } elseif ($collectCodeCoverage) {
            $append                 = !$risky && !$incomplete && !$skipped && !$test->wasRejectedByAssumption();
            $coveredUnintentionally = false;

            if (!$append) {
                $coversTargets = false;
                $usesTargets   = null;
            }

            try {
                CodeCoverage::instance()->stop(
                    $append,
                    $coversTargets,
                    $usesTargets,
                );
            } catch (UnintentionallyCoveredCodeException $cce) {
                $coveredUnintentionally = true;

                $this->emitter->testConsideredRisky(
                    $test->valueObjectForEvents(),
                    CodeCoverage::unintentionallyCoveredCodeMessage($cce),
                );
            } catch (CodeCoverageException) {
                $error = true;
            }

            if ($append && !$error && !$failure && !$coveredUnintentionally &&
                $this->configuration->requireCoverageContribution()) {
                $contributedToCodeCoverage = CodeCoverage::instance()->lastTestContributedToCoverage();

                $test->setContributedToCodeCoverage($contributedToCodeCoverage);

                $riskyBecauseOfNoContribution = $this->riskyTestChecks->didNotContributeToCodeCoverage($contributedToCodeCoverage);

                // whether a property contributes to code coverage is decided
                // on all of its examples
                if ($riskyBecauseOfNoContribution !== null && !$test->isPropertyExample()) {
                    $this->emitter->testConsideredRisky(
                        $test->valueObjectForEvents(),
                        $riskyBecauseOfNoContribution,
                    );
                }
            }
        }

        ErrorHandler::instance()->disable();

        // whether a property performs assertions is decided on all of its
        // examples
        if (!$error &&
            !$incomplete &&
            !$skipped &&
            !$test->isPropertyExample() &&
            $riskyBecauseOfNoAssertions !== null) {
            $this->emitter->testConsideredRisky(
                $test->valueObjectForEvents(),
                $riskyBecauseOfNoAssertions,
            );
        }

        $riskyBecauseOfUnexpectedAssertions = $this->riskyTestChecks->performedUnexpectedAssertions(
            $test->numberOfAssertionsPerformed(),
            $test->doesNotPerformAssertions(),
        );

        if (!$test->isPropertyExample() && $riskyBecauseOfUnexpectedAssertions !== null) {
            $this->emitter->testConsideredRisky(
                $test->valueObjectForEvents(),
                $riskyBecauseOfUnexpectedAssertions,
            );
        }

        if ($test->hasUnexpectedOutput()) {
            $this->emitter->testPrintedUnexpectedOutput($test->output());
        }

        if ($this->configuration->disallowTestOutput() && $test->hasUnexpectedOutput()) {
            $this->emitter->testConsideredRisky(
                $test->valueObjectForEvents(),
                sprintf(
                    'Test code or tested code printed unexpected output: %s',
                    rtrim($test->output(), "\r\n"),
                ),
            );
        }

        if ($test->wasPrepared()) {
            $this->emitter->testFinished(
                $test->valueObjectForEvents(),
                $test->numberOfAssertionsPerformed(),
            );
        }
    }

    private function lacksRequiredCoverageMetadata(TestCase $test): bool
    {
        return $this->requiresCoverageMetadata($test) &&
               !$this->hasCoverageMetadata($test::class, $test->name());
    }

    private function requiresCoverageMetadata(TestCase $test): bool
    {
        $size = $test->size();

        if ($size->isSmall()) {
            return $this->configuration->requireCoverageMetadataOnSmallTests();
        }

        if ($size->isMedium()) {
            return $this->configuration->requireCoverageMetadataOnMediumTests();
        }

        if ($size->isLarge()) {
            return $this->configuration->requireCoverageMetadataOnLargeTests();
        }

        return $this->configuration->requireCoverageMetadata();
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    private function hasCoverageMetadata(string $className, string $methodName): bool
    {
        if (MetadataRegistry::parser()->forClassAndMethod($className, $methodName)->isCoversNothing()->isNotEmpty()) {
            return true;
        }

        return (new CodeCoverageMetadataApi)->coversTargets($className, $methodName)->isNotEmpty();
    }

    private function canTimeLimitBeEnforced(): bool
    {
        return (new Invoker)->canInvokeWithTimeout();
    }

    private function shouldTimeLimitBeEnforced(TestCase $test): bool
    {
        if (!$this->configuration->enforceTimeLimit()) {
            return false;
        }

        if (!(($this->configuration->defaultTimeLimit() > 0 || $test->size()->isKnown()))) {
            return false;
        }

        if (extension_loaded('xdebug') && xdebug_is_debugger_active()) {
            // a debugging session cannot be active while the tests for PHPUnit
            // itself are run
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return true;
    }

    /**
     * @throws Throwable
     */
    private function runTestWithTimeout(TestCase $test): bool
    {
        $_timeout = $this->configuration->defaultTimeLimit();
        $testSize = $test->size();

        if ($testSize->isSmall()) {
            $_timeout = $this->configuration->timeoutForSmallTests();
        } elseif ($testSize->isMedium()) {
            $_timeout = $this->configuration->timeoutForMediumTests();
        } elseif ($testSize->isLarge()) {
            $_timeout = $this->configuration->timeoutForLargeTests();
        }

        try {
            (new Invoker)->invoke($test->runLifecycle(...), [], $_timeout);
        } catch (TimeoutException) {
            // an example of a property that exceeds its time limit falsifies
            // the property, which is reported as an error
            if (!$test->isPropertyExample()) {
                $this->emitter->testConsideredRisky(
                    $test->valueObjectForEvents(),
                    sprintf(
                        'This test was aborted after %d second%s',
                        $_timeout,
                        $_timeout !== 1 ? 's' : '',
                    ),
                );
            }

            return true;
        }

        return false;
    }

    private function performSanityChecks(TestCase $test, TargetCollection $coversTargets, TargetCollection $usesTargets, bool $coversNothingContradiction): void
    {
        if ($coversNothingContradiction) {
            $this->emitter->testTriggeredPhpunitWarning(
                $test->valueObjectForEvents(),
                '#[Covers*] and #[Uses*] attributes do not have an effect when the #[CoversNothing] attribute is used',
            );
        }

        $coversAsString = [];
        $usesAsString   = [];

        foreach ($coversTargets as $coversTarget) {
            $coversAsString[] = $coversTarget->description();
        }

        foreach ($usesTargets as $usesTarget) {
            $usesAsString[] = $usesTarget->description();
        }

        $coversDuplicates = array_unique(array_diff_assoc($coversAsString, array_unique($coversAsString)));
        $usesDuplicates   = array_unique(array_diff_assoc($usesAsString, array_unique($usesAsString)));
        $coversAndUses    = array_intersect($coversAsString, $usesAsString);

        foreach ($coversDuplicates as $target) {
            $this->emitter->testTriggeredPhpunitWarning(
                $test->valueObjectForEvents(),
                sprintf(
                    '%s is targeted multiple times by the same "Covers" attribute',
                    $target,
                ),
            );
        }

        foreach ($usesDuplicates as $target) {
            $this->emitter->testTriggeredPhpunitWarning(
                $test->valueObjectForEvents(),
                sprintf(
                    '%s is targeted multiple times by the same "Uses" attribute',
                    $target,
                ),
            );
        }

        foreach ($coversAndUses as $target) {
            $this->emitter->testTriggeredPhpunitWarning(
                $test->valueObjectForEvents(),
                sprintf(
                    '%s is targeted by both "Covers" and "Uses" attributes',
                    $target,
                ),
            );
        }
    }

    private function removeFilesystemTargetsThatAreNotFirstPartyCode(TestCase $test, TargetCollection $targets): TargetCollection
    {
        if (!$this->configuration->source()->notEmpty()) {
            return $targets;
        }

        $filteredTargets = [];
        $warnings        = [];

        foreach ($targets as $target) {
            if ($this->isFilesystemTargetThatIsNotFirstPartyCode($target)) {
                $warnings[] = sprintf(
                    '%s is outside of the code that is configured to be first-party code using <source>, the attribute is ignored',
                    $target->description(),
                );

                continue;
            }

            $filteredTargets[] = $target;
        }

        foreach (array_unique($warnings) as $warning) {
            $this->emitter->testTriggeredPhpunitWarning(
                $test->valueObjectForEvents(),
                $warning,
            );
        }

        return TargetCollection::fromArray($filteredTargets);
    }

    private function isFilesystemTargetThatIsNotFirstPartyCode(Target $target): bool
    {
        if (!$target->isFile() && !$target->isDirectory() && !$target->isDirectoryRecursively()) {
            return false;
        }

        $path = realpath($target->target());

        if ($path === false) {
            // paths that cannot be resolved are reported by phpunit/php-code-coverage's target validation
            return false;
        }

        if ($target->isFile()) {
            return !SourceFilter::instance()->includes($path);
        }

        return !$this->isDirectoryConfiguredAsFirstPartyCode($path);
    }

    private function isDirectoryConfiguredAsFirstPartyCode(string $directory): bool
    {
        foreach ($this->configuration->source()->includeDirectories() as $includeDirectory) {
            $includeDirectoryPath = realpath($includeDirectory->path());

            if ($includeDirectoryPath === false) {
                continue;
            }

            if ($directory === $includeDirectoryPath) {
                return true;
            }

            if (str_starts_with($directory, $includeDirectoryPath . DIRECTORY_SEPARATOR)) {
                return true;
            }
        }

        return false;
    }
}
