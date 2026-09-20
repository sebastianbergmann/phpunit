--TEST--
phpunit --generate-configuration uses the XSD of PHPUnit installed with Composer when it is available
--STDIN--




--FILE--
<?php declare(strict_types=1);
$directory = sys_get_temp_dir() . '/phpunit-generate-configuration-with-composer-install';

@mkdir($directory . '/vendor/phpunit/phpunit', 0777, true);
touch($directory . '/vendor/phpunit/phpunit/phpunit.xsd');

$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--generate-configuration';

require_once __DIR__ . '/../../bootstrap.php';
chdir($directory);

register_shutdown_function(
    static function () use ($directory): void
    {
        preg_match('/xsi:noNamespaceSchemaLocation="([^"]+)"/', file_get_contents($directory . '/phpunit.xml'), $matches);

        print PHP_EOL . 'Schema location: ' . $matches[1] . PHP_EOL;
    },
);

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Generating phpunit.xml in %s

Bootstrap script (relative to path shown above; default: vendor/autoload.php): Tests directory (relative to path shown above; default: tests): Source directory (relative to path shown above; default: src): Cache directory (relative to path shown above; default: .phpunit.cache): 
Generated phpunit.xml in %s.
Make sure to exclude the .phpunit.cache directory from version control.

Schema location: vendor/phpunit/phpunit/phpunit.xsd
--CLEAN--
<?php declare(strict_types=1);
$directory = sys_get_temp_dir() . '/phpunit-generate-configuration-with-composer-install';

@unlink($directory . '/phpunit.xml');
@unlink($directory . '/vendor/phpunit/phpunit/phpunit.xsd');
@rmdir($directory . '/vendor/phpunit/phpunit');
@rmdir($directory . '/vendor/phpunit');
@rmdir($directory . '/vendor');
@rmdir($directory);
