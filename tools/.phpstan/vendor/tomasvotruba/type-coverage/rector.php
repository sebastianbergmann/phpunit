<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets()
    ->withPreparedSets(deadCode: true, codeQuality: true, codingStyle: true, typeDeclarations: true, privatization: true, naming: true)
    ->withImportNames(removeUnusedImports: true)
    ->withSkip([
        '*/Fixture/*',
        '*/Source/*',
    ]);
