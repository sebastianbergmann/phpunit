--TEST--
#[Retry] with JUnit XML logging reports the duration of all attempts, not just the deciding one
--FILE--
<?php declare(strict_types=1);
$junitFile = tempnam(sys_get_temp_dir(), 'phpunit_retry_junit_duration_');

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $junitFile;
$_SERVER['argv'][] = __DIR__ . '/_files/DurationAllAttemptsFailTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$document = new DOMDocument;
$document->load($junitFile);

unlink($junitFile);

$xpath = new DOMXPath($document);

// Each of the three attempts of each test sleeps for 150 milliseconds, so the
// duration of every test must cover all of them (~450ms). A single attempt
// would be ~150ms.
$everyTestCaseCoversAllAttempts = true;

foreach ($xpath->query('//testcase') as $testCase) {
    if ((float) $testCase->getAttribute('time') < 0.3) {
        $everyTestCaseCoversAllAttempts = false;
    }
}

// The time of the test suite wrapping a retried test method is the sum of the
// times of the test cases it contains, so it must match.
$everyInnerSuiteMatchesItsTestCase = true;

foreach ($xpath->query('//testsuite/testsuite') as $innerSuite) {
    $testCase = $xpath->query('testcase', $innerSuite)->item(0);

    if (abs((float) $innerSuite->getAttribute('time') - (float) $testCase->getAttribute('time')) >= 0.001) {
        $everyInnerSuiteMatchesItsTestCase = false;
    }
}

print "\n";

if ($everyTestCaseCoversAllAttempts) {
    print "testcase duration covers all attempts: yes\n";
} else {
    print "testcase duration covers all attempts: no\n";
}

if ($everyInnerSuiteMatchesItsTestCase) {
    print "testsuite duration matches testcase duration: yes\n";
} else {
    print "testsuite duration matches testcase duration: no\n";
}
--EXPECTF--
%A
testcase duration covers all attempts: yes
testsuite duration matches testcase duration: yes
