--TEST--
The API for running examples of properties one at a time cannot be booted again when booting it failed after the global state was changed
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api');

foreach (['invalid.xml', 'phpunit.xml', 'invalid-without-failing-on-phpunit-warning.xml'] as $configurationFile) {
    try {
        PropertyRunner::boot($configurationFile);
    } catch (PHPUnit\Exception $e) {
        print $e::class . ': ' . explode("\n", $e->getMessage())[0] . PHP_EOL;
    }
}
--EXPECT--
PHPUnit\Runner\PropertyBased\WarningTriggeredWhileBootingException: Booting PHPUnit triggered a warning:
PHPUnit\Runner\PropertyBased\AlreadyBootedException: PHPUnit cannot be booted again in this process because booting it failed before, after it had changed global state that cannot be reset
PHPUnit\Runner\PropertyBased\AlreadyBootedException: PHPUnit cannot be booted again in this process because booting it failed before, after it had changed global state that cannot be reset
