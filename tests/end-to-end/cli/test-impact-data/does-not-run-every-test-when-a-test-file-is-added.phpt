--TEST--
A test file that is added to the directory of a test suite runs the tests in it, and not every test
--FILE--
<?php declare(strict_types=1);
$added = __DIR__ . '/_files/watched-files/tests/AddedTest.php';

function run(array $additionalArguments = []): void
{
    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/watched-files/phpunit.xml',
            ...$additionalArguments,
        ],
        [1 => ['pipe', 'w']],
        $pipes,
    );

    $output = stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    proc_close($process);

    foreach (preg_split('/\R/', $output) as $line) {
        if (str_starts_with($line, 'Impact:') || str_starts_with($line, 'OK') || str_starts_with($line, 'Tests:') || str_starts_with($line, 'No tests executed')) {
            print $line . PHP_EOL;
        }
    }
}

run();

file_put_contents(
    $added,
    '<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\TestImpactData\WatchedFiles;

use PHPUnit\Framework\TestCase;

final class AddedTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
',
);

print PHP_EOL . 'AddedTest.php was added:' . PHP_EOL;

run(['--only-impacted']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

$added = __DIR__ . '/_files/watched-files/tests/AddedTest.php';

if (is_file($added)) {
    unlink($added);
}

delete_directory(__DIR__ . '/_files/watched-files/.phpunit.cache');
--EXPECT--
OK (1 test, 1 assertion)

AddedTest.php was added:
Impact:        1 of 2 tests can be affected by what changed; 1 test is not run
OK (1 test, 1 assertion)
