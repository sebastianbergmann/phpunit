--TEST--
Repeat option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'random';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatDependentTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTREGEX--
(FSSS....)|(FS..SS..)|(FS....SS)|(..FSSS..)|(..FS..SS)|(....FSSS)
