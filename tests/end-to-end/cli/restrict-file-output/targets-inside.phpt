--TEST--
The test run proceeds when every output path is inside the directory specified with --restrict-file-output
--FILE--
<?php declare(strict_types=1);
$allowed = sys_get_temp_dir() . '/phpunit-restrict-file-output-targets-inside';

mkdir($allowed);

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--restrict-file-output';
$_SERVER['argv'][] = $allowed;
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $allowed . '/cache';
$_SERVER['argv'][] = '--log-junit';
$_SERVER['argv'][] = $allowed . '/logs/junit.xml';
$_SERVER['argv'][] = __DIR__ . '/../../_files/restrict-file-output/ExampleTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

var_dump(is_file($allowed . '/logs/junit.xml'));
var_dump(is_file($allowed . '/cache/test-run-history'));
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
bool(true)
bool(true)
--CLEAN--
<?php declare(strict_types=1);
$allowed = sys_get_temp_dir() . '/phpunit-restrict-file-output-targets-inside';

unlink($allowed . '/logs/junit.xml');
rmdir($allowed . '/logs');
unlink($allowed . '/cache/test-run-history');
rmdir($allowed . '/cache');
rmdir($allowed);
