#!/usr/bin/env php
<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require __DIR__ . '/../../vendor/autoload.php';

use SebastianBergmann\Version;

$buffer  = \file_get_contents(__DIR__ . '/../../src/Runner/Version.php');
$start   = \strpos($buffer, 'new VersionId(\'') + \strlen('new VersionId(\'');
$end     = \strpos($buffer, '\'', $start);
$version = \substr($buffer, $start, $end - $start);
$version = new Version($version, __DIR__ . '/../../');

print $version->getVersion();
