--TEST--
The right events are emitted in the right order for a test that is considered risky because it modified global state
--SKIPIF--
<?php declare(strict_types=1);
if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: this test does not work on Windows / GitHub Actions\n";
}
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--globals-backup';
$_SERVER['argv'][] = '--strict-global-state';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/RiskyBecauseGlobalStateModificationTest.php';

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main(false);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
Test Runner Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (1 test)
Test Suite Sorted
Event Facade Sealed
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\RiskyBecauseGlobalStateModificationTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\RiskyBecauseGlobalStateModificationTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\RiskyBecauseGlobalStateModificationTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\RiskyBecauseGlobalStateModificationTest::testOne)
Test Considered Risky (PHPUnit\TestFixture\Event\RiskyBecauseGlobalStateModificationTest::testOne)
This test modified global state but was not expected to do so
--- Global variables before the test
+++ Global variables after the test
@@ @@
         '59a8c89424fb59abbafb7b9e7d35c3bf' => true
         'f7e4bfe68a5b92f823e4904bf540ba11' => true
     )
+    'variable' => 'value'
 )
Test Finished (PHPUnit\TestFixture\Event\RiskyBecauseGlobalStateModificationTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\RiskyBecauseGlobalStateModificationTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
