<?php declare(strict_types=1);
if (extension_loaded('pcov')) {
    return;
}

if (!extension_loaded('xdebug')) {
    print 'skip: This test requires a code coverage driver';
}

if (version_compare(phpversion('xdebug'), '3.1', '>=') && in_array('coverage', xdebug_info('mode'), true)) {
    return;
}

$mode = getenv('XDEBUG_MODE');

if ($mode === false || $mode === '') {
    $mode = ini_get('xdebug.mode');
}

if ($mode === false ||
    !in_array('coverage', explode(',', $mode), true)) {
    print 'skip: XDEBUG_MODE=coverage or xdebug.mode=coverage has to be set';
}
