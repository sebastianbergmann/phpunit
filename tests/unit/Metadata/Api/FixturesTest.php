<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Api;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use function array_map;
use function basename;
use function file_put_contents;
use function mkdir;
use function realpath;
use function rmdir;
use function scandir;
use function sort;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\TestImpactAnalysis\TestThatUsesFixtures;
use PHPUnit\TestFixture\TestImpactAnalysis\TestWithAMissingDataProviderClass;

#[CoversClass(Fixtures::class)]
#[Small]
#[Group('metadata')]
final class FixturesTest extends TestCase
{
    /**
     * @var list<non-empty-string>
     */
    private array $directories = [];

    protected function tearDown(): void
    {
        foreach ($this->directories as $directory) {
            $entries = scandir($directory);

            if ($entries !== false) {
                foreach ($entries as $entry) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }

                    unlink($directory . DIRECTORY_SEPARATOR . $entry);
                }
            }

            rmdir($directory);
        }

        $this->directories = [];
    }

    public function testResolvesAPathThatIsDeclaredOnTheTestClass(): void
    {
        $this->assertContains(
            'scenarios',
            $this->baseNamesOfFixturesFor('testDeclaredOnTheMethod'),
        );
    }

    public function testResolvesAPathThatIsDeclaredOnTheTestMethod(): void
    {
        $this->assertSame(
            ['one.txt', 'scenarios'],
            $this->baseNamesOfFixturesFor('testDeclaredOnTheMethod'),
        );
    }

    public function testResolvesAPathThatIsDeclaredOnTheDataProvider(): void
    {
        $this->assertSame(
            ['scenarios', 'sums.csv'],
            $this->baseNamesOfFixturesFor('testDeclaredOnTheDataProvider'),
        );
    }

    public function testResolvesAPathRelativeToTheFileItIsDeclaredIn(): void
    {
        $this->assertContains(
            realpath(__DIR__ . '/../../../_files/TestImpactAnalysis/fixtures/one.txt'),
            (new Fixtures)->for(TestThatUsesFixtures::class, 'testDeclaredOnTheMethod'),
        );
    }

    public function testDoesNotResolveAPathThatIsNotThere(): void
    {
        $this->assertSame(
            ['scenarios'],
            $this->baseNamesOfFixturesFor('testDeclaredButNotThere'),
        );
    }

    public function testReportsAPathThatCannotBeResolved(): void
    {
        $this->assertSame(
            ['fixtures/does-not-exist.csv'],
            (new Fixtures)->thatCannotBeResolved(TestThatUsesFixtures::class, 'testDeclaredButNotThere'),
        );
    }

    public function testResolvesNoPathThroughADataProviderWhoseClassCannotBeFound(): void
    {
        $this->assertSame([], (new Fixtures)->for(TestWithAMissingDataProviderClass::class, 'testOne'));
    }

    public function testReportsNoPathWhenEveryPathCanBeResolved(): void
    {
        $this->assertSame(
            [],
            (new Fixtures)->thatCannotBeResolved(TestThatUsesFixtures::class, 'testDeclaredOnTheMethod'),
        );
    }

    public function testResolvesAPathThatIsAbsolute(): void
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-fixtures-' . uniqid();

        mkdir($directory);

        $resolvedDirectory = realpath($directory);

        $this->assertIsString($resolvedDirectory);

        $fixture   = $resolvedDirectory . DIRECTORY_SEPARATOR . 'fixture.csv';
        $className = 'TestThatUsesAnAbsoluteFixturePath' . uniqid();
        $classFile = $resolvedDirectory . DIRECTORY_SEPARATOR . $className . '.php';

        file_put_contents($fixture, 'a');

        file_put_contents(
            $classFile,
            <<<PHP
                <?php declare(strict_types=1);
                use PHPUnit\Framework\Attributes\UsesFixture;
                use PHPUnit\Framework\TestCase;

                #[UsesFixture('{$fixture}')]
                final class {$className} extends TestCase
                {
                    public function testOne(): void
                    {
                    }
                }
                PHP,
        );

        require_once $classFile;

        try {
            /** @var class-string $className */
            $this->assertSame([$fixture], (new Fixtures)->for($className, 'testOne'));
        } finally {
            unlink($fixture);
            unlink($classFile);
            rmdir($resolvedDirectory);
        }
    }

    public function testResolvesAPathWhoseNameIsANumber(): void
    {
        $directory = $this->temporaryDirectory();
        $fixture   = $directory . DIRECTORY_SEPARATOR . '2024';

        file_put_contents($fixture, 'a');

        $className = $this->writeTestClass(
            $directory,
            'TestThatUsesAFixtureNamedByANumber',
            "#[UsesFixture('2024')]",
            '',
        );

        $this->assertSame([$fixture], (new Fixtures)->for($className, 'testOne'));
    }

    public function testResolvesTheSamePathDeclaredInTwoDirectories(): void
    {
        $directoryOfTheTest     = $this->temporaryDirectory();
        $directoryOfTheProvider = $this->temporaryDirectory();

        $fixtureOfTheTest     = $directoryOfTheTest . DIRECTORY_SEPARATOR . 'data.txt';
        $fixtureOfTheProvider = $directoryOfTheProvider . DIRECTORY_SEPARATOR . 'data.txt';

        file_put_contents($fixtureOfTheTest, 'a');
        file_put_contents($fixtureOfTheProvider, 'b');

        $provider = $this->writeTestClass(
            $directoryOfTheProvider,
            'ProviderThatUsesAFixture',
            "#[UsesFixture('data.txt')]",
            'public static function provide(): array { return [[1]]; }',
        );

        $className = $this->writeTestClass(
            $directoryOfTheTest,
            'TestThatUsesTheSameFixturePath',
            "#[UsesFixture('data.txt')]",
            "#[DataProviderExternal(\\{$provider}::class, 'provide')]" . PHP_EOL . '    public function testTwo(int $a): void {}',
        );

        $fixtures = (new Fixtures)->for($className, 'testTwo');

        sort($fixtures);

        $expected = [$fixtureOfTheProvider, $fixtureOfTheTest];

        sort($expected);

        $this->assertSame($expected, $fixtures);
    }

    /**
     * @return non-empty-string
     */
    private function temporaryDirectory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-fixtures-' . uniqid();

        mkdir($directory);

        $resolved = realpath($directory);

        $this->assertIsString($resolved);
        $this->assertNotSame('', $resolved);

        $this->directories[] = $resolved;

        return $resolved;
    }

    /**
     * @param non-empty-string $directory
     * @param non-empty-string $name
     *
     * @return class-string
     */
    private function writeTestClass(string $directory, string $name, string $attribute, string $body): string
    {
        $className = $name . uniqid();
        $file      = $directory . DIRECTORY_SEPARATOR . $className . '.php';

        file_put_contents(
            $file,
            <<<PHP
                <?php declare(strict_types=1);
                use PHPUnit\Framework\Attributes\DataProviderExternal;
                use PHPUnit\Framework\Attributes\UsesFixture;
                use PHPUnit\Framework\TestCase;

                {$attribute}
                final class {$className} extends TestCase
                {
                    public function testOne(): void
                    {
                    }

                    {$body}
                }
                PHP,
        );

        require_once $file;

        /** @var class-string $className */
        return $className;
    }

    /**
     * @param non-empty-string $methodName
     *
     * @return list<non-empty-string>
     */
    private function baseNamesOfFixturesFor(string $methodName): array
    {
        $baseNames = array_map(
            static fn (string $path): string => basename($path),
            (new Fixtures)->for(TestThatUsesFixtures::class, $methodName),
        );

        sort($baseNames);

        return $baseNames;
    }
}
