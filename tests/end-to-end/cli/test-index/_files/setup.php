<?php declare(strict_types=1);

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Writes two test classes, in two groups, to a directory of their own so that
 * they can be changed while the index for them exists.
 */
function setUpTestFiles(): string
{
    $directory = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . 'phpunit-test-index-' . \uniqid();

    \mkdir($directory);
    \mkdir($directory . '/tests');

    /*
     * The test files are selected through a test suite in an XML configuration
     * file: that is the only way of selecting them that consults the index.
     */
    \file_put_contents(
        $directory . '/phpunit.xml',
        <<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit cacheDirectory="cache" recordTestRunHistory="false" cacheTestIndex="true">
                <testsuites>
                    <testsuite name="default">
                        <directory>tests</directory>
                    </testsuite>
                </testsuites>
            </phpunit>
            XML,
    );

    writeTestClass($directory, 'One', 'a');
    writeTestClass($directory, 'Two', 'b');

    return $directory;
}

function writeTestClass(string $directory, string $name, string $group): void
{
    \file_put_contents(
        $directory . '/tests/' . $name . 'Test.php',
        <<<PHP
            <?php declare(strict_types=1);
            namespace PHPUnit\TestFixture\TestIndex;

            use PHPUnit\Framework\Attributes\Group;
            use PHPUnit\Framework\TestCase;

            final class {$name}Test extends TestCase
            {
                #[Group('{$group}')]
                public function testInGroup{$group}(): void
                {
                    \$this->assertTrue(true);
                }
            }
            PHP,
    );
}

/**
 * Writes a test class whose test method has a named data set. The name of that
 * data set is not knowable without invoking the data provider.
 */
function writeTestClassWithDataProvider(string $directory, string $name, string $dataSetName): void
{
    \file_put_contents(
        $directory . '/tests/' . $name . 'Test.php',
        <<<PHP
            <?php declare(strict_types=1);
            namespace PHPUnit\TestFixture\TestIndex;

            use PHPUnit\Framework\Attributes\DataProvider;
            use PHPUnit\Framework\TestCase;

            final class {$name}Test extends TestCase
            {
                public static function provider(): array
                {
                    return ['{$dataSetName}' => [true]];
                }

                #[DataProvider('provider')]
                public function testWithDataSet(bool \$value): void
                {
                    \$this->assertTrue(\$value);
                }
            }
            PHP,
    );
}

/**
 * Writes a PHPT test file, and extends the test suite in the XML configuration
 * file to select PHPT files as well. A PHPT file is not a PHP file: it must
 * never be loaded as one, not even to index it.
 */
function writePhptFile(string $directory, string $name): void
{
    \file_put_contents(
        $directory . '/tests/' . $name . '.phpt',
        <<<'PHPT'
            --TEST--
            A PHPT test
            --FILE--
            <?php declare(strict_types=1);
            print 'the PHPT test was run';
            --EXPECT--
            the PHPT test was run
            PHPT,
    );

    \file_put_contents(
        $directory . '/phpunit.xml',
        <<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <phpunit cacheDirectory="cache" recordTestRunHistory="false" cacheTestIndex="true">
                <testsuites>
                    <testsuite name="default">
                        <directory>tests</directory>
                        <directory suffix=".phpt">tests</directory>
                    </testsuite>
                </testsuites>
            </phpunit>
            XML,
    );
}

/**
 * Writes a test class that makes PHPUnit warn about it while it is loaded.
 */
function writeTestClassThatWarns(string $directory, string $name, string $group): void
{
    \file_put_contents(
        $directory . '/tests/' . $name . 'Test.php',
        <<<PHP
            <?php declare(strict_types=1);
            namespace PHPUnit\TestFixture\TestIndex;

            use PHPUnit\Framework\Attributes\Before;
            use PHPUnit\Framework\Attributes\Group;
            use PHPUnit\Framework\TestCase;

            final class {$name}Test extends TestCase
            {
                #[Before]
                #[Group('{$group}')]
                public function testIsAlsoAHookMethod(): void
                {
                }

                #[Group('{$group}')]
                public function testInGroup{$group}(): void
                {
                    \$this->assertTrue(true);
                }
            }
            PHP,
    );
}

/**
 * Reports what PHPUnit had to say about the run, without the parts of the
 * output that differ from run to run.
 */
function warningsFor(string $directory, string $group): string
{
    $output = run(
        [
            '--configuration',
            $directory . '/phpunit.xml',
            '--group',
            $group,
            '--no-progress',
        ],
        false,
    );

    $lines = [];

    foreach (\explode("\n", $output) as $line) {
        if (\str_starts_with($line, 'Tests: ') || \str_starts_with($line, 'OK ') || \str_contains($line, 'test runner warning')) {
            $lines[] = $line;
        }
    }

    return \implode("\n", $lines) . "\n";
}

/**
 * Writes a test class whose file triggers a deprecation while it is being
 * loaded, and not while a test in it is being run.
 */
function writeTestClassThatDeprecatesWhileItIsLoaded(string $directory, string $name, string $group): void
{
    \file_put_contents(
        $directory . '/tests/' . $name . 'Test.php',
        <<<PHP
            <?php declare(strict_types=1);
            namespace PHPUnit\TestFixture\TestIndex;

            use PHPUnit\Framework\Attributes\Group;
            use PHPUnit\Framework\TestCase;

            \\trigger_error('deprecation from the file itself', \\E_USER_DEPRECATED);

            final class {$name}Test extends TestCase
            {
                #[Group('{$group}')]
                public function testInGroup{$group}(): void
                {
                    \$this->assertTrue(true);
                }
            }
            PHP,
    );
}

/**
 * Reports what PHPUnit had to say about a test file that is named on its own on
 * the command line, without the parts of the output that differ from run to
 * run. The index this uses is the one the runs that use the XML configuration
 * file use.
 */
function issuesForFile(string $directory, string $name): string
{
    $output = run(
        [
            '--no-configuration',
            '--do-not-record-test-run-history',
            '--cache-directory',
            $directory . '/cache',
            '--cache-test-index',
            '--no-progress',
            $directory . '/tests/' . $name . 'Test.php',
        ],
        false,
    );

    $lines = [];

    foreach (\explode("\n", $output) as $line) {
        if (\str_starts_with($line, 'Tests: ') || \str_starts_with($line, 'OK ') || \str_contains($line, 'test runner warning')) {
            $lines[] = $line;
        }
    }

    return \implode("\n", $lines) . "\n";
}

/**
 * Reports how running every test went, without the parts of the output that
 * differ from run to run.
 */
function resultFor(string $directory): string
{
    $output = run(
        [
            '--configuration',
            $directory . '/phpunit.xml',
            '--no-progress',
        ],
        false,
    );

    $lines = [];

    foreach (\explode("\n", $output) as $line) {
        if (\str_starts_with($line, 'Tests: ') || \str_starts_with($line, 'OK ')) {
            $lines[] = $line;
        }
    }

    return \implode("\n", $lines) . "\n";
}

/**
 * Lists the tests whose name matches the given filter.
 */
function listTestsMatching(string $directory, string $filter): string
{
    return run(
        [
            '--configuration',
            $directory . '/phpunit.xml',
            '--filter',
            $filter,
            '--list-tests',
        ],
    );
}

/**
 * Lists the tests in the given group, selecting them through the test suite in
 * the XML configuration file.
 */
function listTests(string $directory, string $group): string
{
    return run(
        [
            '--configuration',
            $directory . '/phpunit.xml',
            '--group',
            $group,
            '--list-tests',
        ],
    );
}

/**
 * Lists the tests in the given group, selecting them by naming a directory on
 * the command line instead of using the test suite in the XML configuration
 * file.
 */
function listTestsInDirectory(string $directory, string $group): string
{
    return run(
        [
            '--no-configuration',
            '--do-not-record-test-run-history',
            '--cache-directory',
            $directory . '/cache',
            '--cache-test-index',
            '--group',
            $group,
            $directory . '/tests',
            '--list-tests',
        ],
    );
}

/**
 * @param list<string> $arguments
 */
function run(array $arguments, bool $onlyTests = true): string
{
    $process = \proc_open(
        [
            \PHP_BINARY,
            __DIR__ . '/../../../../../phpunit',
            ...$arguments,
        ],
        [
            1 => ['pipe', 'w'],
        ],
        $pipes,
    );

    $output = \stream_get_contents($pipes[1]);

    \fclose($pipes[1]);
    \proc_close($process);

    if (!$onlyTests) {
        return $output;
    }

    $tests = [];

    foreach (\explode("\n", $output) as $line) {
        if (\str_starts_with($line, ' - ')) {
            $tests[] = $line;
        }
    }

    \sort($tests);

    return \implode("\n", $tests) . "\n";
}

function tearDownTestFiles(string $directory): void
{
    foreach (['/tests', '/cache', ''] as $subdirectory) {
        $path = $directory . $subdirectory;

        if (!\is_dir($path)) {
            continue;
        }

        foreach (\scandir($path) as $entry) {
            if ($entry === '.' || $entry === '..' || \is_dir($path . \DIRECTORY_SEPARATOR . $entry)) {
                continue;
            }

            \unlink($path . \DIRECTORY_SEPARATOR . $entry);
        }

        \rmdir($path);
    }
}
