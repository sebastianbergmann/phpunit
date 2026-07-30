<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\SelfTest;

use const DIRECTORY_SEPARATOR;
use function assert;
use function dirname;
use function is_dir;
use function sort;
use function sprintf;
use function str_contains;
use function str_replace;
use function str_starts_with;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

#[CoversNothing]
#[Medium]
final class TestSuiteConfigurationTest extends TestCase
{
    public function testEveryPathReferencedByTheEndToEndTestSuiteExists(): void
    {
        $missing = [];

        foreach ([...self::directories(), ...self::excludes()] as $path) {
            if (!is_dir(self::projectDirectory() . '/' . $path)) {
                $missing[] = $path;
            }
        }

        $this->assertSame(
            [],
            $missing,
            'phpunit.xml references directories that do not exist',
        );
    }

    public function testEveryEndToEndTestIsPartOfTheEndToEndTestSuite(): void
    {
        $directories = self::directories();
        $excludes    = self::excludes();

        $notSelected = [];

        foreach (self::endToEndTests() as $test) {
            if (!self::isSelected($test, $directories, $excludes)) {
                $notSelected[] = $test;
            }
        }

        $this->assertSame(
            [],
            $notSelected,
            'These end-to-end tests are not part of the "end-to-end" test suite; ' .
            'add their directory to phpunit.xml or move them into a fixture directory',
        );
    }

    /**
     * @return list<non-empty-string>
     */
    private static function directories(): array
    {
        return self::configuredPaths('directory');
    }

    /**
     * @return list<non-empty-string>
     */
    private static function excludes(): array
    {
        return self::configuredPaths('exclude');
    }

    /**
     * @param non-empty-string $element
     *
     * @return list<non-empty-string>
     */
    private static function configuredPaths(string $element): array
    {
        $document = new DOMDocument;
        $loaded   = $document->load(self::projectDirectory() . '/phpunit.xml');

        assert($loaded !== false);

        $nodes = new DOMXPath($document)->query(
            sprintf('//testsuite[@name="end-to-end"]/%s', $element),
        );

        assert($nodes !== false);

        $paths = [];

        foreach ($nodes as $node) {
            $paths[] = $node->textContent;
        }

        return $paths;
    }

    /**
     * @return list<non-empty-string>
     */
    private static function endToEndTests(): array
    {
        $tests = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                self::projectDirectory() . '/tests/end-to-end',
                RecursiveDirectoryIterator::SKIP_DOTS,
            ),
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'phpt') {
                continue;
            }

            $path = str_replace(
                self::projectDirectory() . '/',
                '',
                str_replace(DIRECTORY_SEPARATOR, '/', $file->getPathname()),
            );

            if (self::isFixture($path)) {
                continue;
            }

            $tests[] = $path;
        }

        sort($tests);

        return $tests;
    }

    /**
     * @param non-empty-string $path
     */
    private static function isFixture(string $path): bool
    {
        if (str_contains($path, '/_files/')) {
            return true;
        }

        if (str_contains($path, '/fixture/')) {
            return true;
        }

        return str_starts_with($path, 'tests/end-to-end/phar/');
    }

    /**
     * @param non-empty-string       $path
     * @param list<non-empty-string> $directories
     * @param list<non-empty-string> $excludes
     */
    private static function isSelected(string $path, array $directories, array $excludes): bool
    {
        if (array_any($excludes, fn($exclude) => str_starts_with($path, $exclude . '/'))) {
            return false;
        }

        return array_any($directories, fn($directory) => str_starts_with($path, $directory . '/'));

    }

    /**
     * @return non-empty-string
     */
    private static function projectDirectory(): string
    {
        return dirname(__DIR__, 2);
    }
}
