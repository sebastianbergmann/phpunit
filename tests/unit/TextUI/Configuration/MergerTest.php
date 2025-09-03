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
use PHPUnit\Framework\Attributes\Ticket;
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

    #[Ticket('https://github.com/sebastianbergmann/phpunit/issues/6340')]
    public function testIssue6340(): void
    {
        $fromFile = (new Loader)->load(TEST_FILES_PATH . 'configuration-issue-6340.xml');

        $this->assertTrue($fromFile->phpunit()->failOnPhpunitDeprecation());
        $this->assertTrue($fromFile->phpunit()->failOnDeprecation());
        $this->assertTrue($fromFile->phpunit()->failOnNotice());
        $this->assertTrue($fromFile->phpunit()->failOnWarning());
        $this->assertTrue($fromFile->phpunit()->failOnIncomplete());
        $this->assertTrue($fromFile->phpunit()->failOnSkipped());

        $fromCli = (new Builder)->fromParameters([
            '--do-not-fail-on-phpunit-deprecation',
            '--do-not-fail-on-deprecation',
            '--do-not-fail-on-notice',
            '--do-not-fail-on-warning',
            '--do-not-fail-on-incomplete',
            '--do-not-fail-on-skipped',
        ]);

        $this->assertTrue($fromCli->doNotFailOnPhpunitDeprecation());
        $this->assertTrue($fromCli->doNotFailOnDeprecation());
        $this->assertTrue($fromCli->doNotFailOnNotice());
        $this->assertTrue($fromCli->doNotFailOnWarning());
        $this->assertTrue($fromCli->doNotFailOnIncomplete());
        $this->assertTrue($fromCli->doNotFailOnSkipped());

        $mergedConfig = (new Merger)->merge($fromCli, $fromFile);

        $this->assertTrue($mergedConfig->doNotFailOnPhpunitDeprecation());
        $this->assertTrue($mergedConfig->doNotFailOnDeprecation());
        $this->assertTrue($mergedConfig->doNotFailOnNotice());
        $this->assertTrue($mergedConfig->doNotFailOnWarning());
        $this->assertTrue($mergedConfig->doNotFailOnIncomplete());
        $this->assertTrue($mergedConfig->doNotFailOnSkipped());

        $this->assertFalse($mergedConfig->displayDetailsOnPhpunitDeprecations());
        $this->assertFalse($mergedConfig->displayDetailsOnTestsThatTriggerDeprecations());
        $this->assertFalse($mergedConfig->displayDetailsOnTestsThatTriggerNotices());
        $this->assertFalse($mergedConfig->displayDetailsOnTestsThatTriggerWarnings());
        $this->assertFalse($mergedConfig->displayDetailsOnIncompleteTests());
        $this->assertFalse($mergedConfig->displayDetailsOnSkippedTests());
    }
}
