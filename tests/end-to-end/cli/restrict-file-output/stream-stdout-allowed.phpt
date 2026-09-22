--TEST--
php://stdout is allowed as an output target when --restrict-file-output is used
--FILE--
<?php declare(strict_types=1);
$allowed = sys_get_temp_dir() . '/phpunit-restrict-file-output-stream-stdout-allowed';

mkdir($allowed);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--restrict-file-output';
$_SERVER['argv'][] = $allowed;
$_SERVER['argv'][] = '--testdox-text';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../../_files/restrict-file-output/ExampleTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)Example (PHPUnit\TestFixture\RestrictFileOutput\Example)
 [x] One



Time: %s, Memory: %s

OK (1 test, 1 assertion)
--CLEAN--
<?php declare(strict_types=1);
rmdir(sys_get_temp_dir() . '/phpunit-restrict-file-output-stream-stdout-allowed');
