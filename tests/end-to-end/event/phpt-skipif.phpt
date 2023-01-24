--TEST--
The right events are emitted in the right order for a skipped PHPT test
--SKIPIF--
<?php declare(strict_types=1);
if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: this test does not work on Windows / GitHub Actions\n";
}
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-skipif-location-hint-example.phpt';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (1 test)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (1 test)
Test Preparation Started (%s/phpt-skipif-location-hint-example.phpt)
Test Prepared (%s/phpt-skipif-location-hint-example.phpt)
Test Skipped (%s/phpt-skipif-location-hint-example.phpt)
something terrible happened
Test Finished (%s/phpt-skipif-location-hint-example.phpt)
Test Suite Finished (1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
