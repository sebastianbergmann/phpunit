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

        (new TestSuiteFilterProcessor)->process(
            $this->configuration($file),
            TestSuite::empty('test suite'),
        );
    }

    private function configuration(string $testIdFilterFile): Configuration
    {
        return (new Merger)->merge(
            (new CliBuilder)->fromParameters([
                '--test-id-filter-file',
                $testIdFilterFile,
            ]),
            DefaultConfiguration::create(),
        );
    }
}
