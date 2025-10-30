--TEST--
Repeat option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = __DIR__ . '/_files/phpt/failure.phpt';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

FS                                                                  2 / 2 (100%)

Time: %s, Memory: %s MB

There was 1 failure:

1) %s/tests/end-to-end/repeat/_files/phpt/failure.phpt
Failed asserting that string matches format description.
--- Expected
+++ Actual
@@ @@
-ko
+ok

%s/tests/end-to-end/repeat/_files/phpt/failure.phpt:%d

FAILURES!
Tests: 2, Assertions: 1, Failures: 1, Skipped: 1.

