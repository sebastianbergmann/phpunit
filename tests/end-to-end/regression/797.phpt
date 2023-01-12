--TEST--
GH-797: Disabled $preserveGlobalState does not load bootstrap.php.
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][]  = '--process-isolation';
$_SERVER['argv'][]  = '--bootstrap';
$_SERVER['argv'][]  = __DIR__ . '/797/bootstrap797.php';
$_SERVER['argv'][]  = __DIR__ . '/797/Issue797Test.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
