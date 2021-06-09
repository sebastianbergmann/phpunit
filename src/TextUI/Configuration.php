<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use function assert;
use function is_dir;
use function is_file;
use function is_readable;
use function realpath;
use function substr;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\TestSuiteLoader;
use PHPUnit\TextUI\CliArguments\Configuration as CliConfiguration;
use PHPUnit\TextUI\XmlConfiguration\Configuration as XmlConfiguration;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;
use Throwable;

/**
 * CLI options and XML configuration are static within a single PHPUnit process.
 * It is therefore okay to use a Singleton registry here.
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Configuration
{
    private static ?self $instance = null;

    private ?TestSuite $testSuite;

    private ?string $bootstrap;

    public static function get(): self
    {
        assert(self::$instance instanceof self);

        return self::$instance;
    }

    public static function initFromCli(CliConfiguration $cliConfiguration): void
    {
        $bootstrap = null;

        if ($cliConfiguration->hasBootstrap()) {
            $bootstrap = $cliConfiguration->bootstrap();
        }

        if ($bootstrap !== null) {
            self::handleBootstrap($bootstrap);
        }

        $testSuite = null;

        if ($cliConfiguration->hasArgument()) {
            $argument = realpath($cliConfiguration->argument());

            if (!$argument) {
                throw new TestFileNotFoundException($cliConfiguration->argument());
            }

            $testSuite = self::testSuiteFromPath(
                $argument,
                self::testSuffixes($cliConfiguration)
            );
        }

        self::$instance = new self($testSuite, $bootstrap);
    }

    /**
     * @throws TestFileNotFoundException
     */
    public static function initFromCliAndXml(CliConfiguration $cliConfiguration, XmlConfiguration $xmlConfiguration): void
    {
        $bootstrap = null;

        if ($cliConfiguration->hasBootstrap()) {
            $bootstrap = $cliConfiguration->bootstrap();
        } elseif ($xmlConfiguration->phpunit()->hasBootstrap()) {
            $bootstrap = $xmlConfiguration->phpunit()->bootstrap();
        }

        if ($bootstrap !== null) {
            self::handleBootstrap($bootstrap);
        }

        if ($cliConfiguration->hasArgument()) {
            $argument = realpath($cliConfiguration->argument());

            if (!$argument) {
                throw new TestFileNotFoundException($cliConfiguration->argument());
            }

            $testSuite = self::testSuiteFromPath(
                $argument,
                self::testSuffixes($cliConfiguration)
            );
        } else {
            $includeTestSuite = '';

            if ($cliConfiguration->hasTestSuite()) {
                $includeTestSuite = $cliConfiguration->testSuite();
            } elseif ($xmlConfiguration->phpunit()->hasDefaultTestSuite()) {
                $includeTestSuite = $xmlConfiguration->phpunit()->defaultTestSuite();
            }

            $testSuite = (new TestSuiteMapper)->map(
                $xmlConfiguration->testSuite(),
                $includeTestSuite,
                $cliConfiguration->hasExcludedTestSuite() ? $cliConfiguration->excludedTestSuite() : ''
            );
        }

        self::$instance = new self($testSuite, $bootstrap);
    }

    private function __construct(?TestSuite $testSuite, ?string $bootstrap)
    {
        $this->testSuite = $testSuite;
        $this->bootstrap = $bootstrap;
    }

    /**
     * @psalm-assert-if-true !null $this->testSuite
     */
    public function hasTestSuite(): bool
    {
        return $this->testSuite !== null;
    }

    /**
     * @throws NoTestSuiteException
     */
    public function testSuite(): TestSuite
    {
        if ($this->testSuite === null) {
            throw new NoTestSuiteException;
        }

        return $this->testSuite;
    }

    /**
     * @psalm-assert-if-true !null $this->bootstrap
     */
    public function hasBootstrap(): bool
    {
        return $this->bootstrap !== null;
    }

    /**
     * @throws NoBootstrapException
     */
    public function bootstrap(): string
    {
        if ($this->bootstrap === null) {
            throw new NoBootstrapException;
        }

        return $this->bootstrap;
    }

    /**
     * @psalm-param list<string> $suffixes
     */
    private static function testSuiteFromPath(string $path, array $suffixes): TestSuite
    {
        if (is_dir($path)) {
            $files = (new FileIteratorFacade)->getFilesAsArray($path, $suffixes);

            $suite = new TestSuite($path);
            $suite->addTestFiles($files);

            return $suite;
        }

        if (is_file($path) && substr($path, -5, 5) === '.phpt') {
            $suite = new TestSuite;
            $suite->addTestFile($path);

            return $suite;
        }

        try {
            $testClass = (new TestSuiteLoader)->load($path);
        } catch (\PHPUnit\Exception $e) {
            print $e->getMessage() . PHP_EOL;

            exit(1);
        }

        return new TestSuite($testClass);
    }

    private static function testSuffixes(CliConfiguration $cliConfiguration): array
    {
        $testSuffixes = ['Test.php', '.phpt'];

        if ($cliConfiguration->hasTestSuffixes()) {
            $testSuffixes = $cliConfiguration->testSuffixes();
        }

        return $testSuffixes;
    }

    /**
     * @throws InvalidBootstrapException
     */
    private static function handleBootstrap(string $filename): void
    {
        if (!is_readable($filename)) {
            throw new InvalidBootstrapException($filename);
        }

        try {
            include $filename;
        } catch (Throwable $t) {
            throw new BootstrapException($t);
        }

        Facade::emitter()->bootstrapFinished($filename);
    }
}
