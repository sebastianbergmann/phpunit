--TEST--
Code units in the PHPUnit\ or SebastianBergmann\ namespace that do not belong to PHPUnit or one of its dependencies, or whose source file does not exist, are not preloaded
--FILE--
<?php declare(strict_types=1);
$vendorDirectory = sys_get_temp_dir() . '/phpunit-preload-fixture/vendor';

if (!is_dir($vendorDirectory . '/composer')) {
    mkdir($vendorDirectory . '/composer', 0777, true);
}

file_put_contents(
    $vendorDirectory . '/autoload.php',
    '<?php require ' . var_export(realpath(__DIR__ . '/../../../vendor/autoload.php'), true) . ';',
);

file_put_contents(
    $vendorDirectory . '/Foo.php',
    '<?php declare(strict_types=1); namespace SebastianBergmann\Foo; final class Foo extends \Acme\ClassThatDoesNotExist {}',
);

file_put_contents(
    $vendorDirectory . '/composer/autoload_classmap.php',
    '<?php return ' . var_export(
        [
            'SebastianBergmann\Foo\Foo' => $vendorDirectory . '/Foo.php',
            'SebastianBergmann\Foo\Bar' => $vendorDirectory . '/Bar.php',
        ],
        true,
    ) . ';',
);

$process = proc_open(
    [
        PHP_BINARY,
        __DIR__ . '/../_files/preload/phpunit.php',
        $vendorDirectory,
        '--do-not-record-test-run-history',
        '--no-configuration',
        __DIR__ . '/../_files/basic/SuccessTest.php',
    ],
    [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ],
    $pipes,
);

print stream_get_contents($pipes[1]);
fclose($pipes[1]);

print stream_get_contents($pipes[2]);
fclose($pipes[2]);

proc_close($process);
--CLEAN--
<?php declare(strict_types=1);
$directory = sys_get_temp_dir() . '/phpunit-preload-fixture';

@unlink($directory . '/vendor/composer/autoload_classmap.php');
@unlink($directory . '/vendor/autoload.php');
@unlink($directory . '/vendor/Foo.php');
@rmdir($directory . '/vendor/composer');
@rmdir($directory . '/vendor');
@rmdir($directory);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

OK (1 test, 1 assertion)
