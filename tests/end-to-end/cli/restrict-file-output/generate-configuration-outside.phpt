--TEST--
The configuration file written by --generate-configuration is checked against --restrict-file-output
--FILE--
<?php declare(strict_types=1);
$allowed = sys_get_temp_dir() . '/phpunit-restrict-file-output-generate-configuration-outside';

mkdir($allowed);
chdir(sys_get_temp_dir());

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--restrict-file-output';
$_SERVER['argv'][] = $allowed;
$_SERVER['argv'][] = '--generate-configuration';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot proceed because the following paths are outside %sphpunit-restrict-file-output-generate-configuration-outside, the directory that --restrict-file-output allows writing to:

  %sphpunit.xml (generated configuration file)
--CLEAN--
<?php declare(strict_types=1);
rmdir(sys_get_temp_dir() . '/phpunit-restrict-file-output-generate-configuration-outside');
