<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (!defined('TEST_FILES_PATH')) {
    define('TEST_FILES_PATH', __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR);
}

$composer = file_exists(__DIR__ . '/../vendor/autoload.php');
$phar     = file_exists(__DIR__ . '/autoload.php');

if ($composer && $phar) {
    print 'More than one test fixture autoloader is available, exiting.' . PHP_EOL;

    exit(1);
}

if (!$composer && !$phar) {
    print 'No test fixture autoloader was registered, exiting.' . PHP_EOL;

    exit(1);
}

if ($composer) {
    if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
        define('PHPUNIT_COMPOSER_INSTALL', dirname(__DIR__) . '/vendor/autoload.php');
    }

    require_once __DIR__ . '/../vendor/autoload.php';
}

if ($phar) {
    if (!defined('__PHPUNIT_PHAR__')) {
        require_once __DIR__ . '/../build/artifacts/phpunit-snapshot.phar';
    }

    require_once __DIR__ . '/autoload.php';

    $jsonFile = dirname(__DIR__) . '/composer.json';
    $base     = dirname($jsonFile);

    foreach (json_decode(file_get_contents($jsonFile), true)['autoload-dev']['files'] as $file) {
        require_once $base . DIRECTORY_SEPARATOR . $file;
    }
}

unset($composer, $phar, $jsonFile, $base, $file);
