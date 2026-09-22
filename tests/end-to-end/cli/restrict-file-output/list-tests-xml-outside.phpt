--TEST--
The file written by --list-tests-xml is checked against --restrict-file-output
--FILE--
<?php declare(strict_types=1);
$allowed = sys_get_temp_dir() . '/phpunit-restrict-file-output-list-tests-xml-outside';

mkdir($allowed);

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--restrict-file-output';
$_SERVER['argv'][] = $allowed;
$_SERVER['argv'][] = '--list-tests-xml';
$_SERVER['argv'][] = sys_get_temp_dir() . '/tests.xml';
$_SERVER['argv'][] = __DIR__ . '/../../_files/restrict-file-output/ExampleTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot proceed because the following paths are outside %sphpunit-restrict-file-output-list-tests-xml-outside, the directory that --restrict-file-output allows writing to:

  %stests.xml (list of tests in XML format)
--CLEAN--
<?php declare(strict_types=1);
rmdir(sys_get_temp_dir() . '/phpunit-restrict-file-output-list-tests-xml-outside');
