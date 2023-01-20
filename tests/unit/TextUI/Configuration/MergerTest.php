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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\Configuration\Merger;

#[CoversClass(Merger::class)]
#[Medium]
final class MergerTest extends TestCase
{
    public function testNoLoggingShouldOnlyAffectXmlConfiguration(): void
    {
        $junitLog = uniqid('junit_log_');
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration_logging.xml');

        $this->assertTrue($fromFile->logging()->hasTeamCity());
        $this->assertTrue($fromFile->logging()->hasTestDoxHtml());
        $this->assertTrue($fromFile->logging()->hasTestDoxText());

        $this->assertTrue($fromFile->logging()->hasJunit());
        $this->assertNotSame($junitLog, $fromFile->logging()->junit()->target()->path());

        $fromCli = (new Builder)->fromParameters([
            '--no-logging',
            '--log-junit',
            $junitLog,
        ]);

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertFalse($mergedConfig->hasLogfileTeamcity());
        $this->assertFalse($mergedConfig->hasLogfileTestdoxHtml());
        $this->assertFalse($mergedConfig->hasLogfileTestdoxText());

        $this->assertTrue($mergedConfig->hasLogfileJunit());
        $this->assertSame($junitLog, $mergedConfig->logfileJunit());
    }

    public function testNoCoverageShouldOnlyAffectXmlConfiguration(): void
    {
        $phpCoverage = uniqid('php_coverage_');
        $fromFile    = (new Loader)->load(TEST_FILES_PATH . 'configuration_codecoverage.xml');

        $this->assertTrue($fromFile->codeCoverage()->hasClover());
        $this->assertTrue($fromFile->codeCoverage()->hasCobertura());
        $this->assertTrue($fromFile->codeCoverage()->hasCrap4j());
        $this->assertTrue($fromFile->codeCoverage()->hasHtml());
        $this->assertTrue($fromFile->codeCoverage()->hasText());
        $this->assertTrue($fromFile->codeCoverage()->hasXml());

        $this->assertTrue($fromFile->codeCoverage()->hasPhp());
        $this->assertNotSame($phpCoverage, $fromFile->codeCoverage()->php()->target()->path());

        $fromCli = (new Builder)->fromParameters([
            '--no-coverage',
            '--coverage-php',
            $phpCoverage,
        ]);

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
