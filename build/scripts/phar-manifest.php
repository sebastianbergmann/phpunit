#!/usr/bin/env php
<?php declare(strict_types=1);
if ($argc !== 2) {
    fwrite(
        STDERR,
        sprintf(
            '%s /path/to/manifest.txt' . PHP_EOL,
            $argv[0]
        )
    );

    exit(1);
}

$dependencies = dependencies();
$version      = version();

manifest($argv[1], $version, $dependencies);

function manifest(string $outputFilename, string $version, array $dependencies): void
{
    $buffer = 'phpunit/phpunit: ' . $version . "\n";

    foreach ($dependencies as $dependency) {
        $buffer .= $dependency['name'] . ': ' . $dependency['version'];

        if (!preg_match('/^[v= ]*(([0-9]+)(\\.([0-9]+)(\\.([0-9]+)(-([0-9]+))?(-?([a-zA-Z-+][a-zA-Z0-9.\\-:]*)?)?)?)?)$/', $dependency['version'])) {
            $buffer .=  '@' . $dependency['source']['reference'];
        }

        $buffer .=  "\n";
    }

    file_put_contents($outputFilename, $buffer);
}

function dependencies(): array
{
    return json_decode(
        file_get_contents(
            __DIR__ . '/../../composer.lock'
        ),
        true
    )['packages'];
}

function version(): string
{
    $tag = @exec('git describe --tags 2>&1');

    if (strpos($tag, '-') === false && strpos($tag, 'No names found') === false) {
        return $tag;
    }

    $branch = @exec('git rev-parse --abbrev-ref HEAD');
    $hash   = @exec('git log -1 --format="%H"');

    return $branch . '@' . $hash;
}
