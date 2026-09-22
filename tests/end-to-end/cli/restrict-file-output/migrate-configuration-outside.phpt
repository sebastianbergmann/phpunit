--TEST--
The files written by --migrate-configuration are checked against --restrict-file-output
--FILE--
<?php declare(strict_types=1);
$allowed = sys_get_temp_dir() . '/phpunit-restrict-file-output-migrate-configuration-outside';

mkdir($allowed);
chdir(sys_get_temp_dir());
copy(__DIR__ . '/../../migration/_files/migration-from-85/phpunit-8.5.xml', 'restrict-file-output.xml');

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = 'restrict-file-output.xml';
$_SERVER['argv'][] = '--restrict-file-output';
$_SERVER['argv'][] = $allowed;
$_SERVER['argv'][] = '--migrate-configuration';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot proceed because the following paths are outside %sphpunit-restrict-file-output-migrate-configuration-outside, the directory that --restrict-file-output allows writing to:

  %srestrict-file-output.xml (migrated configuration file)
  %srestrict-file-output.xml.bak (backup of the configuration file)
--CLEAN--
<?php declare(strict_types=1);
unlink(sys_get_temp_dir() . '/restrict-file-output.xml');
rmdir(sys_get_temp_dir() . '/phpunit-restrict-file-output-migrate-configuration-outside');

if (is_file(sys_get_temp_dir() . '/restrict-file-output.xml.bak')) {
    print 'The backup file outside the allowed directory was created' . PHP_EOL;

    unlink(sys_get_temp_dir() . '/restrict-file-output.xml.bak');
}
