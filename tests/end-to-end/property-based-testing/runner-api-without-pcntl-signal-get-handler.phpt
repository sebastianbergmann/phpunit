--TEST--
The API for running examples of properties one at a time cannot be booted when the handler of the SIGALRM signal of the caller cannot be restored after an example
--INI--
disable_functions=pcntl_signal_get_handler
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

chdir(__DIR__ . '/_files/runner-api');

try {
    PropertyRunner::boot('phpunit.xml');
} catch (PHPUnit\Exception $e) {
    print $e::class . ': ' . $e->getMessage() . PHP_EOL;
}
--EXPECT--
PHPUnit\Runner\PropertyBased\TimeLimitCannotBeEnforcedException: The time limit of an example cannot be enforced because the pcntl extension is not available
