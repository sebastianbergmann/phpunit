--TEST--
#[Retry] with Open Test Reporting logging reports the duration of all attempts, not just the deciding one
--FILE--
<?php declare(strict_types=1);
$logfile = tempnam(sys_get_temp_dir(), 'phpunit_retry_otr_duration_');

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = $logfile;
$_SERVER['argv'][] = __DIR__ . '/_files/DurationAllAttemptsFailTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

$document = new DOMDocument;
$document->load($logfile);

unlink($logfile);

$xpath = new DOMXPath($document);
$xpath->registerNamespace('phpunit', 'https://schema.phpunit.de/otr/phpunit/0.2.0');

// Each of the three attempts of each test sleeps for 150 milliseconds, so the
// duration of every test (and every test suite that wraps a retried test
// method) must cover all of them (~450ms). Before the duration of tolerated
// attempts was accounted for, the per-test resourceUsage was that of a single
// attempt (~150ms) and did not match the enclosing suite.
$everyResourceUsageCoversAllAttempts = true;

foreach ($xpath->query('//phpunit:resourceUsage') as $resourceUsage) {
    if ((float) $resourceUsage->getAttribute('time') < 0.3) {
        $everyResourceUsageCoversAllAttempts = false;
    }
}

if ($everyResourceUsageCoversAllAttempts) {
    print "duration covers all attempts: yes\n";
} else {
    print "duration covers all attempts: no\n";
}
--EXPECT--
duration covers all attempts: yes
