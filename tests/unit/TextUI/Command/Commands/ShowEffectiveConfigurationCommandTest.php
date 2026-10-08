<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

use const PHP_EOL;
use function realpath;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\CliArguments\Builder as CliConfigurationBuilder;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\Configuration\TestSuiteCollection;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use ReflectionClass;

#[CoversClass(ShowEffectiveConfigurationCommand::class)]
#[Small]
#[Group('textui')]
#[Group('textui/commands')]
final class ShowEffectiveConfigurationCommandTest extends TestCase
{
    public function testShowsEverySettingOfTheConfiguration(): void
    {
        $output = $this->execute($this->configuration());

        foreach (new ReflectionClass(Configuration::class)->getProperties() as $property) {
            // the test suites are shown in a section of their own
            if ((string) $property->getType() === TestSuiteCollection::class) {
                continue;
            }

            $this->assertMatchesRegularExpression(
                '/^  ' . $property->getName() . ':/m',
                $output,
                'Setting ' . $property->getName() . ' is not shown',
            );
        }
    }

    public function testShowsSettingsInAlphabeticalOrder(): void
    {
        $output = $this->execute($this->configuration());

        $this->assertStringContainsString(
            '  failOnPhpunitWarning: true' . PHP_EOL .
            '  failOnRisky: true' . PHP_EOL .
            '  failOnSelfDeprecation: false' . PHP_EOL,
            $output,
        );
    }

    public function testShowsSettingThatIsNotSetAndSettingThatIsSetToEmptyString(): void
    {
        $output = $this->execute($this->configuration());

        $this->assertStringContainsString('  coverageClover: (not set)' . PHP_EOL, $output);
        $this->assertStringContainsString('  includeTestSuite: ""' . PHP_EOL, $output);
        $this->assertStringContainsString('  groups: (none)' . PHP_EOL, $output);
    }

    public function testShowsSettingsThatAreCompoundValues(): void
    {
        $output = $this->execute($this->configuration());

        $this->assertStringContainsString(
            '  extensionBootstrappers:' . PHP_EOL .
            '    - className: PHPUnit\TestFixture\ShowEffectiveConfiguration\Extension' . PHP_EOL .
            '      parameters:' . PHP_EOL .
            '        key: value' . PHP_EOL,
            $output,
        );

        // an object that has exactly one property is shown as the value of that property
        $this->assertStringContainsString(
            '    includePaths:' . PHP_EOL .
            '      - ' . $this->fixturePath('src') . PHP_EOL,
            $output,
        );

        $this->assertStringContainsString(
            '    iniSettings:' . PHP_EOL .
            '      - name: memory_limit' . PHP_EOL .
            '        value: -1' . PHP_EOL,
            $output,
        );

        $this->assertStringContainsString(
            '  testSuffixes:' . PHP_EOL .
            '    - Test.php' . PHP_EOL .
            '    - .phpt' . PHP_EOL,
            $output,
        );
    }

    public function testDoesNotShowListsOfFilesTheWayTheyAreConfigured(): void
    {
        $output = $this->execute($this->configuration());

        $this->assertStringNotContainsString('includeDirectories', $output);
        $this->assertStringNotContainsString('excludeFiles', $output);
        $this->assertStringNotContainsString('  testSuite:', $output);
    }

    public function testShowsTestFilesOfTestSuitesAfterIncludeAndExcludeRulesHaveBeenApplied(): void
    {
        $this->assertStringContainsString(
            PHP_EOL .
            'Test files:' . PHP_EOL .
            '  unit:' . PHP_EOL .
            '    - ' . $this->fixturePath('tests/unit/ExampleTest.php') . PHP_EOL .
            '  integration:' . PHP_EOL .
            '    - ' . $this->fixturePath('tests/integration/IntegrationTest.php') . ' (groups: slow, database)' . PHP_EOL .
            PHP_EOL,
            $this->execute($this->configuration()),
        );
    }

    public function testShowsTestFilesThatAreSelectedOnCommandLineSortedByPath(): void
    {
        $this->assertStringContainsString(
            PHP_EOL .
            'Test files:' . PHP_EOL .
            '  CLI Arguments:' . PHP_EOL .
            '    - ' . $this->fixturePath('tests/integration/IntegrationTest.php') . PHP_EOL .
            '    - ' . $this->fixturePath('tests/unit/ExampleTest.php') . PHP_EOL .
            '    - ' . $this->fixturePath('tests/unit/ExcludedTest.php') . PHP_EOL .
            PHP_EOL,
            $this->execute(
                $this->configuration(
                    // the first parameter is the name of the script that was invoked
                    'phpunit',
                    $this->fixturePath('tests/unit'),
                    $this->fixturePath() . '/tests/../tests/integration/IntegrationTest.php',
                ),
            ),
        );
    }

    public function testShowsSourceFilesAfterIncludeAndExcludeRulesHaveBeenApplied(): void
    {
        $this->assertStringContainsString(
            PHP_EOL .
            'Source files:' . PHP_EOL .
            '  - ' . $this->fixturePath('src/Example.php') . PHP_EOL .
            '  - ' . $this->fixturePath('src/Generated.php') . PHP_EOL .
            PHP_EOL .
            'Source files excluded from code coverage:' . PHP_EOL .
            '  - ' . $this->fixturePath('src/Generated.php') . PHP_EOL .
            PHP_EOL,
            $this->execute($this->configuration()),
        );
    }

    public function testShowsPharExtensions(): void
    {
        $this->assertStringEndsWith(
            PHP_EOL .
            'PHAR extensions:' . PHP_EOL .
            '  - ' . $this->fixturePath('extensions/extension.phar') . PHP_EOL,
            $this->execute($this->configuration()),
        );
    }

    public function testShowsThatThereAreNoFiles(): void
    {
        $configuration = new Merger($this->createStub(Emitter::class))->merge(
            new CliConfigurationBuilder($this->createStub(Emitter::class))->fromParameters([]),
            DefaultConfiguration::create(),
        );

        $this->assertStringEndsWith(
            PHP_EOL .
            'Test files: (none)' . PHP_EOL .
            PHP_EOL .
            'Source files: (none)' . PHP_EOL .
            PHP_EOL .
            'Source files excluded from code coverage: (none)' . PHP_EOL .
            PHP_EOL .
            'PHAR extensions: (none)' . PHP_EOL,
            $this->execute($configuration),
        );
    }

    public function testShowsWhyTestFilesCannotBeResolved(): void
    {
        $result = new ShowEffectiveConfigurationCommand(
            $this->configuration('phpunit', 'does-not-exist'),
            $this->createStub(Emitter::class),
        )->execute();

        $this->assertStringContainsString(
            PHP_EOL . 'Test files: Test file "does-not-exist" not found' . PHP_EOL,
            $result->output(),
        );

        $this->assertSame(Result::FAILURE, $result->shellExitCode());
    }

    public function testShowsControlCharactersAndLineBreaksInSettingsAsEscapeSequences(): void
    {
        $this->assertStringContainsString(
            '  filter: one\u{001B}[31mtwo\u{000A}three' . PHP_EOL,
            $this->execute($this->configuration('--filter', "one\x1b[31mtwo\nthree")),
        );
    }

    private function execute(Configuration $configuration): string
    {
        $result = new ShowEffectiveConfigurationCommand($configuration, $this->createStub(Emitter::class))->execute();

        $this->assertSame(Result::SUCCESS, $result->shellExitCode());

        return $result->output();
    }

    private function configuration(string ...$parameters): Configuration
    {
        return new Merger($this->createStub(Emitter::class))->merge(
            new CliConfigurationBuilder($this->createStub(Emitter::class))->fromParameters($parameters),
            new Loader($this->createStub(Emitter::class))->load($this->fixturePath('phpunit.xml')),
        );
    }

    /**
     * @return non-empty-string
     */
    private function fixturePath(string $path = ''): string
    {
        $path = realpath(__DIR__ . '/../../../../end-to-end/cli/show-effective-configuration/_files/' . $path);

        $this->assertIsString($path);
        $this->assertNotSame('', $path);

        return $path;
    }
}
