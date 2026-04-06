<?php declare(strict_types=1);
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;

$__phpunit_composerAutoload = {composerAutoload};
$__phpunit_phar             = {phar};

ob_start();

$GLOBALS['__PHPUNIT_ISOLATION_EXCLUDE_LIST'][] = '{job}';

if ($__phpunit_composerAutoload) {
    require_once $__phpunit_composerAutoload;

    define('PHPUNIT_COMPOSER_INSTALL', $__phpunit_composerAutoload);
} else if ($__phpunit_phar) {
    require $__phpunit_phar;
}

$__phpunit_coverage = null;

if ('{bootstrap}' !== '') {
    require_once '{bootstrap}';
}

if (class_exists('SebastianBergmann\CodeCoverage\CodeCoverage')) {
    $__phpunit_filter = new Filter;

    $__phpunit_coverage = new CodeCoverage(
        (new Selector)->{driverMethod}($__phpunit_filter),
        $__phpunit_filter
    );

    if ({codeCoverageCacheDirectory}) {
        $__phpunit_coverage->cacheStaticAnalysis({codeCoverageCacheDirectory});
    }

    $__phpunit_coverage->start(__FILE__);
}

register_shutdown_function(
    function() use ($__phpunit_coverage) {
        $output = null;

        if ($__phpunit_coverage) {
            $output = $__phpunit_coverage->stop();
        }

        file_put_contents('{coverageFile}', serialize($output));
    }
);

ob_end_clean();

require '{job}';
