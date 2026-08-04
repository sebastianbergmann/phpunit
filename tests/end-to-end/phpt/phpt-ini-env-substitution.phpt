--TEST--
PHPT runner substitutes {ENV:...} in the INI section when the environment variable is set
--FILE--
<?php declare(strict_types=1);
\putenv('PHPT_INI_ENV_SUBSTITUTION=phpt-ini-env-substitution-value');

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/phpt-ini-env-substitution.phpt';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
