--TEST--
phpunit --configuration ../_files/baseline/use-baseline-in-another-directory/phpunit.xml --generate-baseline
--FILE--
<?php declare(strict_types=1);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/baseline/use-baseline-in-another-directory/phpunit.xml';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)

1 issue was ignored by baseline.
