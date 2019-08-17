<?php
use SebastianBergmann\CodeCoverage\CodeCoverage;

$composerAutoload = {composerAutoload};
$phar             = {phar};

ob_start();

$GLOBALS['__PHPUNIT_ISOLATION_BLACKLIST'][] = '{job}';

if ($composerAutoload) {
    require_once $composerAutoload;
    define('PHPUNIT_COMPOSER_INSTALL', $composerAutoload);
} else if ($phar) {
    require $phar;
}

{globals}
$coverage = null;

if (isset($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
    require_once $GLOBALS['__PHPUNIT_BOOTSTRAP'];
}

if (class_exists('SebastianBergmann\CodeCoverage\CodeCoverage')) {
    $coverage =	new CodeCoverage(null);
    $coverage->start(__FILE__);
}

register_shutdown_function(function() use ($coverage) {
    $output = null;
    if ($coverage) {
        $output = $coverage->stop();
    }
    file_put_contents('{coverageFile}', serialize($output));
});

ob_end_clean();

require '{job}';
