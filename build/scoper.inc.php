<?php                                                                                                                                                                                                   

declare(strict_types=1);

function getWhiteListedClasses(array $classMap, array $folders) : array {
    $whitelistedClasses = [];
    foreach ($classMap as $className => $classPath) {
        if (isWhitelisted($classPath, $folders)) {
            $whitelistedClasses[] = $className;
        }
    }
    
    return $whitelistedClasses;
}

function isWhitelisted(string $classPath, array $folders) : bool {
    foreach ($folders as $folderPath) {
        if(strpos($classPath, $folderPath) !== false) {
            return true;
        }
    }

    return false;
}

$classMap = include(realpath(dirname(__FILE__)).'/../vendor/composer/autoload_classmap.php');

$whitelistFolderClasses = [
    'src/Framework',
    'phpunit/phpunit-mock-objects',
    'phpunit/php-code-coverage',
    'phpunit/php-token-stream', // This must be also whitelisted, because of the dynamic object instantiation in Stream.php
];

return [
    'whitelist' => getWhiteListedClasses($classMap, $whitelistFolderClasses),
];
