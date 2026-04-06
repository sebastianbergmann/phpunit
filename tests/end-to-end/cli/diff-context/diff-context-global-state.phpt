--TEST--
phpunit --diff-context with global state modification
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--strict-global-state';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/bootstrap.php';
$_SERVER['argv'][] = '--diff-context=1';
$_SERVER['argv'][] = __DIR__ . '/_files/GlobalStateDiffContextTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\DiffContext\GlobalStateDiffContextTest::testModifiesGlobalState
This test modified global state but was not expected to do so
--- Global variables before the test
+++ Global variables after the test
@@ @@
         'key07' => 'val07',
-        'key08' => 'val08',
+        'key08' => 'CHANGED',
         'key09' => 'val09',

%sGlobalStateDiffContextTest.php:%d

OK, but there were issues!
Tests: 1, Assertions: 1, Risky: 1.
