--TEST--
phpunit -c ../_files/configuration_xdebug_filter.xml --dump-xdebug-filter 'php://stdout'
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('xdebug')) {
    print 'skip: Extension xdebug is required.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '-c';
$_SERVER['argv'][] = __DIR__ . '/../_files/configuration_xdebug_filter.xml';
$_SERVER['argv'][] = '--dump-xdebug-filter';
$_SERVER['argv'][] = 'php://stderr';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Please note that --dump-xdebug-filter and --prepend are deprecated and will be removed in PHPUnit 10.
<?php declare(strict_types=1);
if (!\function_exists('xdebug_set_filter')) {
    return;
}

\xdebug_set_filter(
    \XDEBUG_FILTER_CODE_COVERAGE,
    \XDEBUG_PATH_WHITELIST,
    [
        %s
    ]
);
Wrote Xdebug filter script to php://stderr
