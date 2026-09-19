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

use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\Metadata\Attribute\RequiresFunctionTest;
use PHPUnit\TestFixture\TestWithDifferentNames;
use PHPUnit\TextUI\CliArguments\Builder;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\Configuration\Registry as ConfigurationRegistry;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use ReflectionProperty;

#[CoversClass(ProcessIsolation::class)]
#[Small]
final class ProcessIsolationTest extends TestCase
{
    public function testIsNotUsedWhenNeitherTestNorConfigurationRequestsIt(): void
    {
        $test = new TestWithDifferentNames('testWithName');

        $this->assertFalse((new ProcessIsolation)->shouldBeUsedFor($test));
    }

    public function testIsUsedWhenTestRequestsIt(): void
    {
        $test = new TestWithDifferentNames('testWithName');

        $test->setRunTestInSeparateProcess(true);

        $this->assertTrue((new ProcessIsolation)->shouldBeUsedFor($test));
    }

    public function testIsUsedWhenConfigurationRequestsIt(): void
    {
        $test = new TestWithDifferentNames('testWithName');

        $this->assertTrue($this->withProcessIsolationConfigured()->shouldBeUsedFor($test));
    }

    public function testIsNotUsedForTestThatAlreadyRunsInIsolation(): void
    {
        $test = new TestWithDifferentNames('testWithName');

        $test->setRunTestInSeparateProcess(true);
        $test->setInIsolation(true);

        $this->assertFalse((new ProcessIsolation)->shouldBeUsedFor($test));
    }

    public function testIsNotUsedForTestWhoseRequirementsAreNotSatisfied(): void
    {
        $test = new RequiresFunctionTest('testOne');

        $test->setRunTestInSeparateProcess(true);

        $this->assertFalse((new ProcessIsolation)->shouldBeUsedFor($test));
    }

    private function withProcessIsolationConfigured(): ProcessIsolation
    {
        $property              = new ReflectionProperty(ConfigurationRegistry::class, 'instance');
        $originalConfiguration = $property->getValue();

        $property->setValue(
            null,
            (new Merger)->merge(
                new Builder($this->createStub(Emitter::class))->fromParameters([]),
                new Loader($this->createStub(Emitter::class))->load(__DIR__ . '/_files/process-isolation.xml'),
            ),
        );

        try {
            return new ProcessIsolation;
        } finally {
            $property->setValue(null, $originalConfiguration);
        }
    }
}
