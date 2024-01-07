<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite as FrameworkTestSuite;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\TextUI\CliArguments\Builder as CliArgumentsBuilder;
use PHPUnit\TextUI\Configuration\Merger as ConfigurationMerger;
use PHPUnit\TextUI\XmlConfiguration\Loader as XmlConfigurationLoader;
use PHPUnit\TextUI\XmlConfiguration\TestSuiteMapper;

#[CoversClass(TestSuiteBuilder::class)]
#[Small]
final class TestSuiteBuilderTest extends TestCase
{
    public function test_Builds_TestSuite_value_object_for_test_suite_loaded_from_XML_configuration_file(): void
    {
        $testSuite = TestSuiteBuilder::from($this->testSuiteFromXmlConfiguration());

        $this->assertTrue($testSuite->isWithName());
        $this->assertStringEndsWith('phpunit.xml', $testSuite->name());
        $this->assertSame(3, $testSuite->count());
        $this->assertSame(3, $testSuite->tests()->count());
        $this->assertCount(3, $testSuite->tests());
    }

    public function testBuildCountWithFilter(): void
    {
        $testSuite     = $this->testSuiteFromXmlConfiguration();
        $filterFactory = new Factory;
        $filterFactory->addIncludeNameFilter('one');
        $testSuite->injectFilter($filterFactory);
        $testSuite = TestSuiteBuilder::from($testSuite);

        $this->assertSame(1, $testSuite->count());
        $this->assertSame(1, $testSuite->tests()->count());
        $this->assertCount(1, $testSuite->tests());
    }

    public function test_Builds_TestSuite_value_object_for_test_case_class(): void
    {
        $testSuite = TestSuiteBuilder::from($this->testSuiteFromXmlConfiguration()->tests()[0]->tests()[0]);

        $this->assertTrue($testSuite->isForTestClass());
        $this->assertSame('PHPUnit\TestFixture\Groups\FooTest', $testSuite->name());
        $this->assertSame(3, $testSuite->count());
        $this->assertCount(3, $testSuite->tests());
    }

    private function testSuiteFromXmlConfiguration(): FrameworkTestSuite
    {
        $cliConfiguration = (new CliArgumentsBuilder)->fromParameters([]);
        $xmlConfiguration = (new XmlConfigurationLoader)->load(__DIR__ . '/../../../../end-to-end/_files/groups/phpunit.xml');
        $configuration    = (new ConfigurationMerger)->merge($cliConfiguration, $xmlConfiguration);

        return (new TestSuiteMapper)->map(
            $configuration->configurationFile(),
            $configuration->testSuite(),
            $configuration->includeTestSuite(),
            $configuration->excludeTestSuite(),
        );
    }
}
