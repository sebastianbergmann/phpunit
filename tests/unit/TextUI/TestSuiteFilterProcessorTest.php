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

use function uniqid;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TextUI\CliArguments\Builder as CliBuilder;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;

#[CoversClass(TestSuiteFilterProcessor::class)]
#[Medium]
#[Group('textui')]
final class TestSuiteFilterProcessorTest extends TestCase
{
    public function testRejectsTestIdFilterFileThatCannotBeRead(): void
    {
        $file = '/path/to/file/that/does/not/exist/' . uniqid('test_id_filter_file_');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot read from ' . $file);

        new TestSuiteFilterProcessor($this->createStub(Emitter::class))->process(
            $this->configuration(['--test-id-filter-file', $file]),
            TestSuite::empty('test suite', $this->createStub(Emitter::class)),
        );
    }

    public function testHasNoFilterWhenNoTestSelectionIsConfigured(): void
    {
        $this->assertNull(
            new TestSuiteFilterProcessor($this->createStub(Emitter::class))->filter(
                $this->configuration([]),
            ),
        );
    }

    public function testHasAFilterWhenATestSelectionIsConfigured(): void
    {
        $this->assertNotNull(
            new TestSuiteFilterProcessor($this->createStub(Emitter::class))->filter(
                $this->configuration(['--filter', 'testOne']),
            ),
        );
    }

    /**
     * @param list<string> $parameters
     */
    private function configuration(array $parameters): Configuration
    {
        return new Merger($this->createStub(Emitter::class))->merge(
            new CliBuilder($this->createStub(Emitter::class))->fromParameters($parameters),
            DefaultConfiguration::create(),
        );
    }
}
