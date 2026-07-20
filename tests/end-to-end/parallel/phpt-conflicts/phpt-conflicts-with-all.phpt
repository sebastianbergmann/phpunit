--TEST--
phpunit --parallel=2 runs PHPT tests whose --CONFLICTS-- section lists "all" on their own, deferred until the other tests have drained
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/plain-phpt-test.phpt';
$_SERVER['argv'][] = __DIR__ . '/_files/conflicts-with-all-phpt-test.phpt';
$_SERVER['argv'][] = __DIR__ . '/_files/also-conflicts-with-all-phpt-test.phpt';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 3 assertions)
