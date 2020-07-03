<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\CliArguments\Configuration;

class ConfigurationTest extends TestCase
{
    public static function configurationPropertyProvider(): Generator
    {
        yield ['argument'];

        yield ['atLeastVersion'];

        yield ['backupGlobals'];

        yield ['backupStaticAttributes'];

        yield ['beStrictAboutChangesToGlobalState'];

        yield ['beStrictAboutResourceUsageDuringSmallTests'];

        yield ['bootstrap'];

        yield ['cacheResult'];

        yield ['cacheResultFile'];

        yield ['checkVersion'];

        yield ['colors'];

        yield ['columns'];

        yield ['configuration'];

        yield ['coverageFilter'];

        yield ['coverageClover'];

        yield ['coverageCrap4J'];

        yield ['coverageHtml'];

        yield ['coveragePhp'];

        yield ['coverageText'];

        yield ['coverageTextShowUncoveredFiles'];

        yield ['coverageTextShowOnlySummary'];

        yield ['coverageXml'];

        yield ['debug'];

        yield ['defaultTimeLimit'];

        yield ['disableCodeCoverageIgnore'];

        yield ['disallowTestOutput'];

        yield ['disallowTodoAnnotatedTests'];

        yield ['enableOutputBuffer'];

        yield ['enforceTimeLimit'];

        yield ['excludeGroups'];

        yield ['executionOrder'];

        yield ['executionOrderDefects'];

        yield ['failOnEmptyTestSuite'];

        yield ['failOnIncomplete'];

        yield ['failOnRisky'];

        yield ['failOnSkipped'];

        yield ['failOnWarning'];

        yield ['filter'];

        yield ['generateConfiguration'];

        yield ['groups'];

        yield ['help'];

        yield ['includePath'];

        yield ['iniSettings'];

        yield ['junitLogfile'];

        yield ['listGroups'];

        yield ['listSuites'];

        yield ['listTests'];

        yield ['listTestsXml'];

        yield ['loader'];

        yield ['noCoverage'];

        yield ['noExtensions'];

        yield ['extensions'];

        yield ['unavailableExtensions'];

        yield ['noInteraction'];

        yield ['noLogging'];

        yield ['printer'];

        yield ['processIsolation'];

        yield ['randomOrderSeed'];

        yield ['repeat'];

        yield ['reportUselessTests'];

        yield ['resolveDependencies'];

        yield ['reverseList'];

        yield ['stderr'];

        yield ['strictCoverage'];

        yield ['stopOnDefect'];

        yield ['stopOnError'];

        yield ['stopOnFailure'];

        yield ['stopOnIncomplete'];

        yield ['stopOnRisky'];

        yield ['stopOnSkipped'];

        yield ['stopOnWarning'];

        yield ['teamcityLogfile'];

        yield ['testdoxExcludeGroups'];

        yield ['testdoxGroups'];

        yield ['testdoxHtmlFile'];

        yield ['testdoxTextFile'];

        yield ['testdoxXmlFile'];

        yield ['testSuffixes'];

        yield ['testSuite'];

        yield ['unrecognizedOrderBy'];

        yield ['useDefaultConfiguration'];

        yield ['verbose'];

        yield ['version'];

        yield ['xdebugFilterFile'];
    }

    /**
     * @testdox Getting uninitialized property $propertyName throws exception
     * @dataProvider configurationPropertyProvider
     */
    public function testCallingGetterOnUndefinedPropertyThrowsException(string $propertyName): void
    {
        $config = $this->createEmptyConfiguration();

        // Supporting ::hasPropertyName() should exist and report true
        $hasPropertyName = "has{$propertyName}";
        $this->assertTrue(method_exists($config, $hasPropertyName), "unimplemented has{$propertyName}");
        $this->assertFalse($config->{$hasPropertyName}(), "{$propertyName} expected to be uninitialized");

        // Check the getter exists and throws an exception
        $this->assertTrue(method_exists($config, $propertyName), "unimplemented {$propertyName}");
        $this->expectException("PHPUnit\TextUI\CliArguments\Exception");
        $this->assertTrue($config->{$propertyName}());
    }

    private function createEmptyConfiguration(): Configuration
    {
        return new Configuration(
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            [],
            null,
            null,
            null,
            null,
            null,
            null,
        );
    }
}
