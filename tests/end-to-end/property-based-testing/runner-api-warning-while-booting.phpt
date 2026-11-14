--TEST--
The API for running examples of properties one at a time cannot be booted when PHPUnit triggers a warning while it is booted
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

try {
    PropertyRunner::boot('invalid.xml');
} catch (PHPUnit\Exception $e) {
    print $e::class . ': ' . $e->getMessage() . PHP_EOL;
}
--EXPECT--
PHPUnit\Runner\PropertyBased\WarningTriggeredWhileBootingException: Booting PHPUnit triggered a warning:

1) Test results may not be as expected because the XML configuration file did not pass validation:

  Line 4:
  - Element 'unknownElement': This element is not expected.
