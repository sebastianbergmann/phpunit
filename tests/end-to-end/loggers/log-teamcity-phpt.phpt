--TEST--
phpunit --log-teamcity php://stdout ../end-to-end/phpt-stderr.phpt
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--log-teamcity',
    'php://stdout',
    \realpath(__DIR__ . '/../phpt-stderr.phpt'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


##teamcity[testCount count='1' flowId='%d']

##teamcity[testStarted name='%send-to-end%ephpt-stderr.phpt' flowId='%d']
.                                                                   1 / 1 (100%)
##teamcity[testFinished name='%send-to-end%ephpt-stderr.phpt' duration='%d' flowId='%d']


Time: %s, Memory: %s

OK (1 test, 1 assertion)
