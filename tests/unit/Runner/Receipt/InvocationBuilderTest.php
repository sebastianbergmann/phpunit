<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Receipt;

use const DIRECTORY_SEPARATOR;
use const PHP_VERSION;
use function getcwd;
use function hash_file;
use function realpath;
use DateTimeImmutable;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\CliArguments\Builder as CliConfigurationBuilder;
use PHPUnit\TextUI\Configuration\BootstrapLoader;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\Merger;
use PHPUnit\TextUI\XmlConfiguration\DefaultConfiguration;
use PHPUnit\TextUI\XmlConfiguration\Loader;

#[CoversClass(InvocationBuilder::class)]
#[UsesClass(Invocation::class)]
#[UsesClass(BootstrapLoader::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/receipt')]
final class InvocationBuilderTest extends TestCase
{
    public function testNamesTheVersionsOfPhpunitAndPhp(): void
    {
        $invocation = $this->build($this->configurationFromCommandLine(), []);

        $this->assertSame(Version::id(), $invocation->phpunitVersion());
        $this->assertSame(PHP_VERSION, $invocation->phpVersion());
    }

    public function testDoesNotConsiderTheNameOfTheScriptToBeAnArgument(): void
    {
        $invocation = $this->build($this->configurationFromCommandLine(), ['phpunit', '--no-configuration', 'tests']);

        $this->assertSame(['--no-configuration', 'tests'], $invocation->arguments());
    }

    public function testConsidersFirstElementThatIsAnOptionToBeAnArgument(): void
    {
        $invocation = $this->build($this->configurationFromCommandLine(), ['--no-configuration', 'tests']);

        $this->assertSame(['--no-configuration', 'tests'], $invocation->arguments());
    }

    public function testHasNoArgumentsWhenPhpunitIsInvokedWithoutArguments(): void
    {
        $this->assertSame([], $this->build($this->configurationFromCommandLine(), [])->arguments());
        $this->assertSame([], $this->build($this->configurationFromCommandLine(), ['phpunit'])->arguments());
    }

    public function testNamesTheWorkingDirectory(): void
    {
        $this->assertSame(getcwd(), $this->build($this->configurationFromCommandLine(), [])->workingDirectory());
    }

    public function testRecordsTheTimeAtWhichItIsBuilt(): void
    {
        $before     = new DateTimeImmutable;
        $invocation = $this->build($this->configurationFromCommandLine(), []);
        $after      = new DateTimeImmutable;

        $this->assertGreaterThanOrEqual($before, $invocation->startTime());
        $this->assertLessThanOrEqual($after, $invocation->startTime());
    }

    public function testNamesNoConfigurationFileWhenNoneIsUsed(): void
    {
        $invocation = $this->build($this->configurationFromCommandLine(), []);

        $this->assertFalse($invocation->hasConfigurationFile());
        $this->assertFalse($invocation->hasConfigurationFileChecksum());
    }

    public function testNamesTheConfigurationFileAndTheChecksumOfItsContents(): void
    {
        $configurationFile = $this->fixture('_files', 'bootstrap-for-test-suite', 'phpunit.xml');

        $invocation = $this->build($this->configurationFromXmlConfigurationFile($configurationFile), []);

        $this->assertTrue($invocation->hasConfigurationFile());
        $this->assertSame($configurationFile, $invocation->configurationFile());
        $this->assertTrue($invocation->hasConfigurationFileChecksum());
        $this->assertSame(hash_file('sha256', $configurationFile), $invocation->configurationFileChecksum());
    }

    public function testNamesNoBootstrapScriptWhenNoneIsConfigured(): void
    {
        $this->assertSame([], $this->build($this->configurationFromCommandLine(), [])->bootstrapScripts());
    }

    public function testNamesTheBootstrapScriptFollowedByTheBootstrapScriptsOfTheTestSuites(): void
    {
        $invocation = $this->build(
            $this->configurationFromXmlConfigurationFile($this->fixture('_files', 'bootstrap-for-test-suite', 'phpunit.xml')),
            [],
        );

        $this->assertSame(
            [
                $this->fixture('_files', 'bootstrap-for-test-suite', 'tests/bootstrap/bootstrap.php'),
                $this->fixture('_files', 'bootstrap-for-test-suite', 'tests/bootstrap/bootstrap_one.php'),
                $this->fixture('_files', 'bootstrap-for-test-suite', 'tests/bootstrap/bootstrap_two.php'),
            ],
            $invocation->bootstrapScripts(),
        );
    }

    public function testDoesNotNameTheBootstrapScriptOfATestSuiteThatIsNotIncluded(): void
    {
        $invocation = $this->build(
            $this->configurationFromXmlConfigurationFile($this->fixture('_files', 'bootstrap-for-test-suite', 'phpunit.xml'), '--testsuite', 'one'),
            [],
        );

        $this->assertSame(
            [
                $this->fixture('_files', 'bootstrap-for-test-suite', 'tests/bootstrap/bootstrap.php'),
                $this->fixture('_files', 'bootstrap-for-test-suite', 'tests/bootstrap/bootstrap_one.php'),
            ],
            $invocation->bootstrapScripts(),
        );
    }

    public function testDoesNotNameTheBootstrapScriptOfATestSuiteThatIsExcluded(): void
    {
        $invocation = $this->build(
            $this->configurationFromXmlConfigurationFile($this->fixture('_files', 'bootstrap-for-test-suite', 'phpunit.xml'), '--exclude-testsuite', 'one'),
            [],
        );

        $this->assertSame(
            [
                $this->fixture('_files', 'bootstrap-for-test-suite', 'tests/bootstrap/bootstrap.php'),
                $this->fixture('_files', 'bootstrap-for-test-suite', 'tests/bootstrap/bootstrap_two.php'),
            ],
            $invocation->bootstrapScripts(),
        );
    }

    public function testNamesNoExtensionsWhenNoneAreConfigured(): void
    {
        $invocation = $this->build($this->configurationFromCommandLine(), []);

        $this->assertSame([], $invocation->extensionClasses());
    }

    public function testNamesTheBootstrapClassesOfExtensions(): void
    {
        $invocation = $this->build(
            $this->configurationFromXmlConfigurationFile($this->fixture('extension-xml', '_files', 'extension-bootstrap', 'phpunit.xml')),
            [],
        );

        $this->assertSame(['PHPUnit\TestFixture\Event\MyExtension\MyExtensionBootstrap'], $invocation->extensionClasses());
    }

    public function testNamesNoExtensionsWhenExtensionsAreNotLoaded(): void
    {
        $invocation = $this->build(
            $this->configurationFromXmlConfigurationFile($this->fixture('extension-xml', '_files', 'extension-bootstrap', 'phpunit.xml'), '--no-extensions'),
            [],
        );

        $this->assertSame([], $invocation->extensionClasses());
    }

    public function testNamesNoPharWhenPhpunitIsNotRunFromOne(): void
    {
        $invocation = $this->build($this->configurationFromCommandLine(), []);

        $this->assertFalse($invocation->hasPhar());
        $this->assertFalse($invocation->hasPharChecksum());
    }

    public function testNamesThePharThatPhpunitIsRunFromAndItsChecksum(): void
    {
        // any file that can be read can stand in for the PHAR
        $phar = $this->fixture('_files', 'bootstrap-for-test-suite', 'phpunit.xml');

        $invocation = $this->build($this->configurationFromCommandLine(), [], $phar);

        $this->assertTrue($invocation->hasPhar());
        $this->assertSame($phar, $invocation->phar());
        $this->assertTrue($invocation->hasPharChecksum());
        $this->assertSame(hash_file('sha256', $phar), $invocation->pharChecksum());
    }

    public function testNamesNoChecksumForAFileThatCannotBeRead(): void
    {
        $phar = $this->fixture('_files', 'does-not-exist.phar');

        $invocation = $this->build($this->configurationFromCommandLine(), [], $phar);

        $this->assertTrue($invocation->hasPhar());
        $this->assertSame($phar, $invocation->phar());
        $this->assertFalse($invocation->hasPharChecksum());
    }

    /**
     * @param list<string>      $argv
     * @param ?non-empty-string $phar
     */
    private function build(Configuration $configuration, array $argv, ?string $phar = null): Invocation
    {
        return new InvocationBuilder(
            new BootstrapLoader($this->createStub(Emitter::class)),
        )->build($configuration, $argv, $phar);
    }

    /**
     * @param non-empty-string $configurationFile
     * @param non-empty-string ...$parameters
     */
    private function configurationFromXmlConfigurationFile(string $configurationFile, string ...$parameters): Configuration
    {
        return new Merger($this->createStub(Emitter::class))->merge(
            new CliConfigurationBuilder($this->createStub(Emitter::class))->fromParameters($parameters),
            new Loader($this->createStub(Emitter::class))->load($configurationFile),
        );
    }

    private function configurationFromCommandLine(): Configuration
    {
        return new Merger($this->createStub(Emitter::class))->merge(
            new CliConfigurationBuilder($this->createStub(Emitter::class))->fromParameters([]),
            DefaultConfiguration::create(),
        );
    }

    /**
     * A path that is configured in an XML configuration file is appended to
     * the directory of that file as it is, so a segment may be such a path.
     *
     * @param non-empty-string ...$segments
     *
     * @return non-empty-string
     */
    private function fixture(string ...$segments): string
    {
        $path = realpath(__DIR__ . '/../../../end-to-end');

        $this->assertIsString($path);

        foreach ($segments as $segment) {
            $path .= DIRECTORY_SEPARATOR . $segment;
        }

        return $path;
    }
}
