<?php declare(strict_types=1);
use PHPUnit\Runner\Phpt\CodeCoverageBootstrapper;

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

if ('{bootstrap}' !== '') {
    require_once '{bootstrap}';
}

$__phpunit_coverage = CodeCoverageBootstrapper::bootstrap({codeCoverageCacheDirectory}, {branchCoverage}, {pathCoverage});

if ($__phpunit_coverage !== null) {
    $__phpunit_coverage->start(__FILE__);
}

register_shutdown_function(
    function() use ($__phpunit_coverage) {
        $output = null;

        if ($__phpunit_coverage !== null) {
            $output = $__phpunit_coverage->stop();
        }

        file_put_contents('{coverageFile}', serialize($output));
    }
);

ob_end_clean();

require '{job}';
