--TEST--
phpunit --parallel=2 --stop-on-failure stops a property that another worker is checking when the run stops between two of its examples, and runs tearDownAfterClass(), so that the property is not checked for the rest of its examples
--FILE--
<?php declare(strict_types=1);
$marker = sys_get_temp_dir() . '/phpunit-parallel-property-stop-on-failure.marker';

@unlink($marker);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/stop-on/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$marked = file_get_contents($marker);

@unlink($marker);

$examples = substr_count($marked, '.');

if ($examples > 0 && $examples < 300) {
    print "the property was checked for some, but not for all of its examples\n";
} else {
    print 'the property was checked for ' . $examples . " examples\n";
}

if (str_ends_with($marked, "tearDownAfterClass\n")) {
    print "tearDownAfterClass\n";
}
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers
Random Seed:   %d

F

Time: %s, Memory: %s

There was 1 failure:

1) PHPUnit\TestFixture\ParallelProperty\AFailsOnceThePropertyIsBeingCheckedTest::testFails
failure

%sAFailsOnceThePropertyIsBeingCheckedTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
the property was checked for some, but not for all of its examples
tearDownAfterClass
