--TEST--
phpunit --log-teamcity php://stdout ../end-to-end/phpt-stderr.phpt
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--log-teamcity';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = \realpath(__DIR__ . '/../end-to-end/phpt-stderr.phpt');

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


##teamcity[testCount count='1' flowId='%d']

##teamcity[testStarted name='%send-to-end%ephpt-stderr.phpt' flowId='%d']
.                                                                   1 / 1 (100%)
##teamcity[testFinished name='%send-to-end%ephpt-stderr.phpt' duration='%d' flowId='%d']


Time: %s, Memory: %s

OK (1 test, 1 assertion)
