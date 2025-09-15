--TEST--
https://github.com/sebastianbergmann/phpunit/pull/6364
--FILE--
<?php declare(strict_types=1);

// Simulate how PHPStorm runs a test class.
$process = proc_open(
    [
        PHP_BINARY,
        __DIR__ . '/../../../../phpunit',
        '--do-not-cache-result',
        '--no-configuration',
        '--filter',
        'PHPUnit\\\\TestFixture\\\\DataProviderFilterTest',
        '--test-suffix',
        'DataProviderFilterTest.php',
        __DIR__ . '/../../../_files',
        '--teamcity',
    ],
    [
        1 => ['pipe', 'w'],
    ],
    $pipes,
);

$stdout = stream_get_contents($pipes[1]);
fclose($pipes[1]);
proc_close($process);

if (preg_match("/##teamcity\\[testStarted name='testTrue with data set #1' locationHint='([^']+)'/", $stdout, $matches) !== 1) {
    echo "Failed to find locationHint.\n";
    echo $stdout;

    return 0;
}

if (preg_match('#php_qn://(?:[A-Z]:)?[^:]*::\\\\(.*)#', $matches[1], $locationHintMatches) !== 1) {
    echo "Failed to parse locationHint.\n";
    echo $matches[1];

    return 0;
}

// Simulate how PHPStorm runs an individual numbered test case
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = '/' . preg_quote($locationHintMatches[1], '/') . '$/';
$_SERVER['argv'][] = '--test-suffix';
$_SERVER['argv'][] = 'DataProviderFilterTest.php';
$_SERVER['argv'][] = __DIR__ . '/../../../_files';
$_SERVER['argv'][] = '--teamcity';

require_once __DIR__ . '/../../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s

##teamcity[testCount count='1' flowId='%s']
##teamcity[testSuiteStarted name='CLI Arguments' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\DataProviderFilterTest' locationHint='php_qn://%sDataProviderFilterTest.php::\PHPUnit\TestFixture\DataProviderFilterTest' flowId='%d']
##teamcity[testSuiteStarted name='testTrue' locationHint='php_qn://%sDataProviderFilterTest.php::\PHPUnit\TestFixture\DataProviderFilterTest::testTrue' flowId='%d']
##teamcity[testStarted name='testTrue with data set #1' locationHint='php_qn://%sDataProviderFilterTest.php::\PHPUnit\TestFixture\DataProviderFilterTest::testTrue with data set #1' flowId='%d']
##teamcity[testFinished name='testTrue with data set #1' duration='%s' flowId='%d']
##teamcity[testSuiteFinished name='testTrue' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\DataProviderFilterTest' flowId='%d']
##teamcity[testSuiteFinished name='CLI Arguments' flowId='%d']
Time: %s, Memory: %s

OK (1 test, 1 assertion)
