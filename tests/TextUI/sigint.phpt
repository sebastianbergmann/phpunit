--TEST--
phpunit ../_files/SleepTest.php^C
--SKIPIF--
<?php
function_exists('pcntl_fork') || die("skip does not have PCNTL extension installed");
function_exists('posix_kill') || die("skip does not have POSIX extension installed");
?>
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = __DIR__.'/../_files/SleepTest.php';

require __DIR__.'/../bootstrap.php';
$pid = pcntl_fork();
if ($pid === -1) {
    echo 'Could not fork process'.PHP_EOL;
    return;
}

if ($pid > 0) {
    usleep(400000);
    posix_kill($pid, SIGINT);
    pcntl_wait($status);
    return;
}

PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.

Time: %s, Memory: %s

OK (2 tests, 1 assertion)
