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

use function uniqid;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\Configuration\Merger;

/**
 * @medium
 */
final class MergerTest extends TestCase
{
    public function testNoLoggingShouldOnlyAffectXmlConfiguration(): void
    {
        $junitLog = uniqid('junit_log_');
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_logging.xml');
        $fromCli  = (new Builder)->fromParameters([
            '--no-logging',
            '--log-junit',
            $junitLog,
        ], []);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->hasLogfileText());
        $this->assertFalse($mergedConfig->hasLogfileTeamcity());
        $this->assertFalse($mergedConfig->hasLogfileTestdoxHtml());
        $this->assertFalse($mergedConfig->hasLogfileTestdoxText());
        $this->assertFalse($mergedConfig->hasLogfileTestdoxXml());

        $this->assertTrue($mergedConfig->hasLogfileJunit());
        $this->assertSame($junitLog, $mergedConfig->logfileJunit());
    }

    public function testNoCoverageShouldOnlyAffectXmlConfiguration(): void
    {
        $phpCoverage = uniqid('php_coverage_');
        $fromFile    = (new Loader)->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');
        $fromCli     = (new Builder)->fromParameters([
            '--no-coverage',
            '--coverage-php',
            $phpCoverage,
        ], []);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->hasCoverageClover());
        $this->assertFalse($mergedConfig->hasCoverageCobertura());
        $this->assertFalse($mergedConfig->hasCoverageCrap4j());
        $this->assertFalse($mergedConfig->hasCoverageHtml());
        $this->assertFalse($mergedConfig->hasCoverageText());
        $this->assertFalse($mergedConfig->hasCoverageXml());

        $this->assertTrue($mergedConfig->hasCoveragePhp());
        $this->assertSame($phpCoverage, $mergedConfig->coveragePhp());
    }
}
