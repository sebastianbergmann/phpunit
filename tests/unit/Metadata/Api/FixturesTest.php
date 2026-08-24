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
use function array_map;
use function basename;
use function file_put_contents;
use function mkdir;
use function realpath;
use function rmdir;
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
