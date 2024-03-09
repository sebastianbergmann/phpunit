#!/usr/bin/env php
<?php declare(strict_types=1);
if ($argc !== 2) {
    print $argv[0] . ' <tag>' . PHP_EOL;

    exit(1);
}

$version       = $argv[1];
$versionSeries = explode('.', $version)[0] . '.' . explode('.', $version)[1];

$file = __DIR__ . '/../../ChangeLog-' . $versionSeries . '.md';

if (!is_file($file) || !is_readable($file)) {
    print $file . ' cannot be read' . PHP_EOL;

    exit(1);
}

$buffer = '';
$append = false;

foreach (file($file) as $line) {
    if (str_starts_with($line, '## [' . $version . ']')) {
        $append = true;

        continue;
    }

    if ($append && (str_starts_with($line, '## ') || str_starts_with($line, '['))) {
        break;
    }

    if ($append) {
        $buffer .= $line;
    }
}

$buffer = trim($buffer);

if ($buffer === '') {
    print 'Unable to extract release notes' . PHP_EOL;

    exit(1);
}

print $buffer . PHP_EOL;
