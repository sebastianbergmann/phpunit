--TEST--
What was recorded for a test that no longer exists is forgotten by a test run that ran every test there is
--FILE--
<?php declare(strict_types=1);
function phpunit(array $additionalArguments): string
{
    $process = proc_open(
        [
            PHP_BINARY,
            __DIR__ . '/../../../../phpunit',
            '--no-progress',
            '--colors=never',
            '--configuration',
            __DIR__ . '/_files/phpunit-pruning.xml',
            ...$additionalArguments,
        ],
        [1 => ['pipe', 'w']],
        $pipes,
    );

    $output = stream_get_contents($pipes[1]);

    fclose($pipes[1]);
    proc_close($process);

    return $output;
}

function run(array $additionalArguments = []): void
{
    foreach (explode("\n", phpunit($additionalArguments)) as $line) {
        if (str_starts_with($line, 'OK') || str_starts_with($line, 'FAILURES') || str_starts_with($line, 'No tests executed')) {
            print $line . PHP_EOL;
        }
    }
}

function listTestsThatDependOnCalculator(): void
{
    $listed = false;

    foreach (explode("\n", phpunit(['--list-tests-that-depend-on', __DIR__ . '/_files/src/Calculator.php'])) as $line) {
        if (str_starts_with($line, ' - ')) {
            print $line . PHP_EOL;

            $listed = true;
        }
    }

    if (!$listed) {
        print 'no test' . PHP_EOL;
    }
}

$test = __DIR__ . '/_files/tests-that-can-be-forgotten/ForgottenTest.php';

file_put_contents(
    $test,
    <<<'PHP'
        <?php declare(strict_types=1);
        namespace PHPUnit\TestFixture\TestImpactData;

        use PHPUnit\Framework\Attributes\CoversClass;
        use PHPUnit\Framework\TestCase;

        #[CoversClass(Calculator::class)]
        final class ForgottenTest extends TestCase
        {
            public function testAddsAsWell(): void
            {
                $this->assertSame(7, (new Calculator)->add(3, 4));
            }
        }
        PHP,
);

run();

print PHP_EOL . 'Recorded:' . PHP_EOL;

listTestsThatDependOnCalculator();

run(['--only-impacted']);

print PHP_EOL . 'After a test run that ran only the tests that can be affected:' . PHP_EOL;

listTestsThatDependOnCalculator();

unlink($test);

run(['--filter', 'KeptTest']);

print PHP_EOL . 'After a test run that ran only some of the tests:' . PHP_EOL;

listTestsThatDependOnCalculator();

run();

print PHP_EOL . 'After a test run that ran every test there is:' . PHP_EOL;

listTestsThatDependOnCalculator();

$failingTest = __DIR__ . '/_files/tests-that-can-be-forgotten/FailingTest.php';

file_put_contents(
    $failingTest,
    <<<'PHP'
        <?php declare(strict_types=1);
        namespace PHPUnit\TestFixture\TestImpactData;

        use PHPUnit\Framework\Attributes\CoversClass;
        use PHPUnit\Framework\TestCase;

        #[CoversClass(Calculator::class)]
        final class FailingTest extends TestCase
        {
            public function testFails(): void
            {
                $this->assertSame(0, (new Calculator)->add(1, 2));
            }
        }
        PHP,
);

run(['--stop-on-failure']);

print PHP_EOL . 'After a test run that stopped at the first failure:' . PHP_EOL;

listTestsThatDependOnCalculator();

unlink($failingTest);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../../_files/delete_directory.php';

foreach (['ForgottenTest', 'FailingTest'] as $test) {
    $file = __DIR__ . '/_files/tests-that-can-be-forgotten/' . $test . '.php';

    if (is_file($file)) {
        unlink($file);
    }
}

delete_directory(__DIR__ . '/_files/.phpunit.cache.pruning');
--EXPECT--
OK (2 tests, 2 assertions)

Recorded:
 - PHPUnit\TestFixture\TestImpactData\ForgottenTest::testAddsAsWell
 - PHPUnit\TestFixture\TestImpactData\KeptTest::testAdds
No tests executed!

After a test run that ran only the tests that can be affected:
 - PHPUnit\TestFixture\TestImpactData\ForgottenTest::testAddsAsWell
 - PHPUnit\TestFixture\TestImpactData\KeptTest::testAdds
OK (1 test, 1 assertion)

After a test run that ran only some of the tests:
 - PHPUnit\TestFixture\TestImpactData\ForgottenTest::testAddsAsWell
 - PHPUnit\TestFixture\TestImpactData\KeptTest::testAdds
OK (1 test, 1 assertion)

After a test run that ran every test there is:
 - PHPUnit\TestFixture\TestImpactData\KeptTest::testAdds
FAILURES!

After a test run that stopped at the first failure:
 - PHPUnit\TestFixture\TestImpactData\FailingTest::testFails
 - PHPUnit\TestFixture\TestImpactData\KeptTest::testAdds
