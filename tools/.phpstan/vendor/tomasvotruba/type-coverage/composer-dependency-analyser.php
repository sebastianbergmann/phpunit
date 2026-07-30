<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return new Configuration()
    // PhpParser classes are provided by phpstan/phpstan, no need to require nikic/php-parser directly
    ->ignoreErrorsOnPackage('nikic/php-parser', [ErrorType::SHADOW_DEPENDENCY])
    // type-perfect test fixtures are static analysis inputs, not real code, and reference 3rd-party classes on purpose
    ->ignoreErrorsOnPath(
        __DIR__ . '/packages/type-perfect/tests',
        [ErrorType::UNKNOWN_CLASS, ErrorType::SHADOW_DEPENDENCY]
    );
