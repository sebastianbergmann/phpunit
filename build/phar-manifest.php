#!/usr/bin/env php
<?php
$components = array(
    'phpunit/dbunit',
    'phpunit/php-code-coverage',
    'phpunit/php-file-iterator',
    'phpunit/php-invoker',
    'phpunit/php-text-template',
    'phpunit/php-timer',
    'phpunit/php-token-stream',
    'phpunit/phpunit-mock-objects',
    'phpunit/phpunit-selenium',
    'sebastian/diff',
    'sebastian/exporter',
    'sebastian/version'
);

print_git_info('phpunit/phpunit', __DIR__ . '/../');

foreach ($components as $component) {
    print_git_info($component, __DIR__ . '/../vendor/' . $component);
}

function print_git_info($component, $path)
{
    if (!is_dir($path)) {
        return;
    }

    print $component . ': ';

    chdir($path);

    $tag = @exec('git describe --tags 2>&1');

    if (strpos($tag, '-') === false) {
        print $tag;
    } else {
        $branch = @exec('git rev-parse --abbrev-ref HEAD');
        $hash   = @exec('git log -1 --format="%H"');
        print $branch . '@' . $hash;
    }

    print "\n";
}
