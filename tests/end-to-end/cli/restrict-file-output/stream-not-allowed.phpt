--TEST--
Streams other than php://stdout and php://stderr are not allowed as output targets when --restrict-file-output is used
--FILE--
<?php declare(strict_types=1);
$allowed = sys_get_temp_dir() . '/phpunit-restrict-file-output-stream-not-allowed';

mkdir($allowed);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--restrict-file-output';
$_SERVER['argv'][] = $allowed;
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'socket://localhost:1234';
$_SERVER['argv'][] = __DIR__ . '/../../_files/restrict-file-output/ExampleTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot proceed because the following paths are outside %sphpunit-restrict-file-output-stream-not-allowed, the directory that --restrict-file-output allows writing to:

  socket://localhost:1234 (TeamCity log)

Only php://stdout and php://stderr are allowed as streams.
--CLEAN--
<?php declare(strict_types=1);
rmdir(sys_get_temp_dir() . '/phpunit-restrict-file-output-stream-not-allowed');
