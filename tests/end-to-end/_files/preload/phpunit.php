<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\TextUI\Application;

// Runs PHPUnit like the phpunit script does for a Composer installation,
// using the vendor directory that is passed as the first argument
$vendorDirectory = $_SERVER['argv'][1];

unset($_SERVER['argv'][1]);

\define('PHPUNIT_COMPOSER_INSTALL', $vendorDirectory . '/autoload.php');

require PHPUNIT_COMPOSER_INSTALL;

(new Application)->run(\array_values($_SERVER['argv']));
