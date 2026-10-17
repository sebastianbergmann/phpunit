<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestIndex;

use const DIRECTORY_SEPARATOR;
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
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\TestImpactAnalysis\InvoiceThatUsesMoneyTest;
use PHPUnit\TestFixture\TestImpactAnalysis\TestThatUsesATrait;
use PHPUnit\TestFixture\TestImpactAnalysis\TestThatUsesFixtures;
use ReflectionClass;

#[CoversClass(TestFiles::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-index')]
final class TestFilesTest extends TestCase
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

    public function testKnowsTheFileATestClassIsDeclaredIn(): void
    {
        $this->assertSame(
            ['InvoiceThatUsesMoneyTest.php'],
            $this->baseNamesOfFilesOf(InvoiceThatUsesMoneyTest::class),
        );
    }

    #[TestDox('Knows the file a data provider that is a method of the test class itself is declared in')]
    public function testKnowsTheFileOfADataProviderOfTheTestClassItself(): void
    {
        $this->assertSame(
            ['TestThatUsesFixtures.php'],
            $this->baseNamesOfFilesOf(TestThatUsesFixtures::class),
        );
    }

    public function testKnowsTheFileATraitOfATestClassIsDeclaredIn(): void
    {
        $this->assertSame(
            ['TestThatUsesATrait.php', 'TraitOfATest.php'],
            $this->baseNamesOfFilesOf(TestThatUsesATrait::class),
        );
    }

    #[TestDox('Knows the files a data provider that is a method of another class is declared in')]
    public function testKnowsTheFilesOfADataProviderOfAnotherClass(): void
    {
        $this->writeTestClassWithExternalDataProvider('ExternallyProvidedFiles');

        $this->assertSame(
            [
                'ExternallyProvidedFilesProvider.php',
                'ExternallyProvidedFilesProviderParent.php',
                'ExternallyProvidedFilesTest.php',
            ],
            $this->baseNamesOfFilesOf('PHPUnit\TestFixture\TestIndex\ExternallyProvidedFilesTest'),
        );
    }

    #[TestDox('Knows nothing when the class a data provider is a method of does not exist')]
    public function testKnowsNothingWhenTheClassOfADataProviderDoesNotExist(): void
    {
        $this->writeTestClassWithMissingDataProviderClass('MissingProviderFiles');

        $this->assertNull(
            TestFiles::of(new ReflectionClass('PHPUnit\TestFixture\TestIndex\MissingProviderFilesTest')),
        );
    }

    private function writeTestClassWithExternalDataProvider(string $name): void
    {
        $directory = $this->temporaryDirectory();

        file_put_contents(
            $directory . DIRECTORY_SEPARATOR . $name . 'ProviderParent.php',
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestIndex;

                abstract class {$name}ProviderParent
                {
                }
                PHP,
        );

        file_put_contents(
            $directory . DIRECTORY_SEPARATOR . $name . 'Provider.php',
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestIndex;

                final class {$name}Provider extends {$name}ProviderParent
                {
                    public static function provide(): array
                    {
                        return [[1]];
                    }
                }
                PHP,
        );

        file_put_contents(
            $directory . DIRECTORY_SEPARATOR . $name . 'Test.php',
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestIndex;

                use PHPUnit\Framework\Attributes\DataProviderExternal;
                use PHPUnit\Framework\TestCase;

                final class {$name}Test extends TestCase
                {
                    #[DataProviderExternal({$name}Provider::class, 'provide')]
                    public function testOne(int \$value): void
                    {
                    }
                }
                PHP,
        );

        require_once $directory . DIRECTORY_SEPARATOR . $name . 'ProviderParent.php';

        require_once $directory . DIRECTORY_SEPARATOR . $name . 'Provider.php';

        require_once $directory . DIRECTORY_SEPARATOR . $name . 'Test.php';
    }

    private function writeTestClassWithMissingDataProviderClass(string $name): void
    {
        $file = $this->temporaryDirectory() . DIRECTORY_SEPARATOR . $name . 'Test.php';

        file_put_contents(
            $file,
            <<<PHP
                <?php declare(strict_types=1);
                namespace PHPUnit\TestFixture\TestIndex;

                use PHPUnit\Framework\Attributes\DataProviderExternal;
                use PHPUnit\Framework\TestCase;

                final class {$name}Test extends TestCase
                {
                    #[DataProviderExternal('PHPUnit\\TestFixture\\TestIndex\\ThereIsNoSuchProviderEither', 'provide')]
                    public function testOne(int \$value): void
                    {
                    }
                }
                PHP,
        );

        require_once $file;
    }

    /**
     * @return non-empty-string
     */
    private function temporaryDirectory(): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-test-files-' . uniqid();

        mkdir($directory);

        $resolved = realpath($directory);

        $this->assertIsString($resolved);
        $this->assertNotSame('', $resolved);

        $this->directories[] = $resolved;

        return $resolved;
    }

    /**
     * @param class-string<TestCase> $className
     *
     * @return list<non-empty-string>
     */
    private function baseNamesOfFilesOf(string $className): array
    {
        $files = TestFiles::of(new ReflectionClass($className));

        $this->assertIsArray($files);

        $baseNames = array_map(
            static fn (string $file): string => basename($file),
            $files,
        );

        sort($baseNames);

        return $baseNames;
    }
}
