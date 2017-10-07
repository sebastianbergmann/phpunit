#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';

use SebastianBergmann\Version;

$buffer  = \file_get_contents(__DIR__ . '/../src/Runner/Version.php');
$start   = \strpos($buffer, 'new VersionId(\'') + \strlen('new VersionId(\'');
$end     = \strpos($buffer, '\'', $start);
$version = \substr($buffer, $start, $end - $start);
$version = new Version($version, __DIR__ . '/../');

print $version->getVersion();
