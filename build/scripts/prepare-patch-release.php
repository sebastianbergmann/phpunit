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
$rootDirectory = dirname(__DIR__, 2);
$versionFile   = $rootDirectory . '/src/Runner/Version.php';

if (!is_file($versionFile) || !is_readable($versionFile) || !is_writable($versionFile)) {
    print $versionFile . ' cannot be read or written' . PHP_EOL;

    exit(1);
}

$versionFileContents = file_get_contents($versionFile);

if (preg_match('/new VersionId\(\'(\d+)\.(\d+)\.(\d+)\'/', $versionFileContents, $matches) !== 1) {
    print 'Unable to determine the current version number from ' . $versionFile . PHP_EOL;

    exit(1);
}

$major         = (int) $matches[1];
$minor         = (int) $matches[2];
$patch         = (int) $matches[3];
$versionSeries = $major . '.' . $minor;
$oldVersion    = $major . '.' . $minor . '.' . $patch;
$newVersion    = $major . '.' . $minor . '.' . ($patch + 1);

$versionFileContents = str_replace(
    'new VersionId(\'' . $oldVersion . '\'',
    'new VersionId(\'' . $newVersion . '\'',
    $versionFileContents,
);

file_put_contents($versionFile, $versionFileContents);

print 'Updated version number from ' . $oldVersion . ' to ' . $newVersion . ' in ' . $versionFile . PHP_EOL;

$changeLogFile = $rootDirectory . '/ChangeLog-' . $versionSeries . '.md';

if (!is_file($changeLogFile) || !is_readable($changeLogFile) || !is_writable($changeLogFile)) {
    print $changeLogFile . ' cannot be read or written' . PHP_EOL;

    exit(1);
}

$changeLogContents = file_get_contents($changeLogFile);
$today             = date('Y-m-d');

$headingPattern = '/^## \[' . preg_quote($newVersion, '/') . '\] - .*$/m';

if (preg_match($headingPattern, $changeLogContents) !== 1) {
    print 'Unable to find the heading for version ' . $newVersion . ' in ' . $changeLogFile . PHP_EOL;

    exit(1);
}

$changeLogContents = preg_replace(
    $headingPattern,
    '## [' . $newVersion . '] - ' . $today,
    $changeLogContents,
);

print 'Set release date of version ' . $newVersion . ' to ' . $today . ' in ' . $changeLogFile . PHP_EOL;

$diffPattern = '/^(\[' . preg_quote($newVersion, '/') . '\]: .*\.\.\.)' . preg_quote($versionSeries, '/') . '$/m';

if (preg_match($diffPattern, $changeLogContents) !== 1) {
    print 'Unable to find the comparison URL for version ' . $newVersion . ' in ' . $changeLogFile . PHP_EOL;

    exit(1);
}

$changeLogContents = preg_replace(
    $diffPattern,
    '${1}' . $newVersion,
    $changeLogContents,
);

file_put_contents($changeLogFile, $changeLogContents);

print 'Updated comparison URL of version ' . $newVersion . ' from branch ' . $versionSeries . ' to ' . $newVersion . ' in ' . $changeLogFile . PHP_EOL;
