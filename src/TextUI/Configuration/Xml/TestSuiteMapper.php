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

use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Exception as FrameworkException;
use PHPUnit\Framework\TestSuite as TestSuiteObject;
use PHPUnit\Runner\TestIndex\NullTestFileSkipper;
use PHPUnit\Runner\TestIndex\TestFileSkipper;
use PHPUnit\TextUI\Configuration\TestFileResolver;
use PHPUnit\TextUI\Configuration\TestSuiteCollection;
use PHPUnit\TextUI\RuntimeException;
use PHPUnit\TextUI\TestDirectoryNotFoundException;
use PHPUnit\TextUI\TestFileNotFoundException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestSuiteMapper
{
    private Emitter $emitter;
    private TestFileSkipper $skipper;

    public function __construct(Emitter $emitter, ?TestFileSkipper $skipper = null)
    {
        if ($skipper === null) {
            $skipper = new NullTestFileSkipper;
        }

        $this->emitter = $emitter;
        $this->skipper = $skipper;
    }

    /**
     * @param non-empty-string       $xmlConfigurationFile
     * @param list<non-empty-string> $includeTestSuites
     * @param list<non-empty-string> $excludeTestSuites
     * @param positive-int           $numberOfRuns
     * @param positive-int           $maxAttempts
     *
     * @throws RuntimeException
     * @throws TestDirectoryNotFoundException
     * @throws TestFileNotFoundException
     */
    public function map(string $xmlConfigurationFile, TestSuiteCollection $configuredTestSuites, array $includeTestSuites, array $excludeTestSuites, int $numberOfRuns = 1, int $maxAttempts = 1): TestSuiteObject
    {
        try {
            $result = TestSuiteObject::empty($xmlConfigurationFile, $this->emitter);

            $resolvedTestSuites = new TestFileResolver($this->emitter)->filesInTestSuites(
                $configuredTestSuites,
                $includeTestSuites,
                $excludeTestSuites,
            );

            foreach ($resolvedTestSuites as $resolvedTestSuite) {
                /*
                 * Whether a test suite has test files at all, and whether a
                 * test file was already added to another test suite, is
                 * decided before, and therefore independently of, whether a
                 * test file has to be loaded.
                 */
                if ($resolvedTestSuite['files'] === []) {
                    continue;
                }

                $testSuite = TestSuiteObject::empty($resolvedTestSuite['name'], $this->emitter);

                foreach ($resolvedTestSuite['files'] as ['path' => $file, 'groups' => $groups]) {
                    if ($this->skipper->canSkipLoading($file, $groups)) {
                        continue;
                    }

                    $this->skipper->record(
                        $file,
                        static function () use ($testSuite, $file, $groups, $numberOfRuns, $maxAttempts): void
                        {
                            $testSuite->addTestFile($file, $groups, $numberOfRuns, $maxAttempts);
                        },
                    );
                }

                $result->addTest($testSuite);
            }

            return $result;
            // @codeCoverageIgnoreStart
        } catch (FrameworkException $e) {
            throw new RuntimeException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
        // @codeCoverageIgnoreEnd
    }
}
