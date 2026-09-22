--TEST--
The cache directory and the test run history are checked against --restrict-file-output
--FILE--
<?php declare(strict_types=1);
$allowed = sys_get_temp_dir() . '/phpunit-restrict-file-output-cache-directory-outside';

mkdir($allowed);

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--restrict-file-output';
$_SERVER['argv'][] = $allowed;
$_SERVER['argv'][] = '--cache-directory';
$_SERVER['argv'][] = $allowed . '-cache';
$_SERVER['argv'][] = __DIR__ . '/../../_files/restrict-file-output/ExampleTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot proceed because the following paths are outside %sphpunit-restrict-file-output-cache-directory-outside, the directory that --restrict-file-output allows writing to:

  %sphpunit-restrict-file-output-cache-directory-outside-cache (cache directory)
  %sphpunit-restrict-file-output-cache-directory-outside-cache%stest-run-history (test run history)

The test run history is recorded by default. Use --do-not-record-test-run-history to not record it, or --cache-directory to record it inside the allowed directory.
--CLEAN--
<?php declare(strict_types=1);
$allowed = sys_get_temp_dir() . '/phpunit-restrict-file-output-cache-directory-outside';

rmdir($allowed);

if (is_dir($allowed . '-cache')) {
    print 'The cache directory outside the allowed directory was created' . PHP_EOL;

    rmdir($allowed . '-cache');
}
