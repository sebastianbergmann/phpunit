--TEST--
phpunit --testdox -c tests/basic/configuration.basic.xml
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--testdox',
    '--colors=never',
    '--disable-buffer',
    '--order-by=reverse',
    realpath(__DIR__ . '/../../_files/DataproviderExecutionOrderTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Dataprovider Execution Order
 ✘ Add more numbers with a dataprovider with data set "1+1=3"
   │
   │ Failed asserting that 2 is identical to 3.
   │
   │ /Users/ewout/proj/phpunit/tests/_files/DataproviderExecutionOrderTest.php:37
   │

 ✔ Add more numbers with a dataprovider with data set "2+1=3"
 ✔ Add more numbers with a dataprovider with data set "1+2=3"
 ✔ Test in the middle that always works
 ✘ Add numbers with a dataprovider with data set "1+1=3"
   │
   │ Failed asserting that 2 is identical to 3.
   │
   │ /Users/ewout/proj/phpunit/tests/_files/DataproviderExecutionOrderTest.php:24
   │

 ✔ Add numbers with a dataprovider with data set "2+1=3"
 ✔ Add numbers with a dataprovider with data set "1+2=3"
 ✔ First test that always works

Time: %s, Memory: %s

Summary of non-successful tests:

Dataprovider Execution Order
 ✘ Add more numbers with a dataprovider with data set "1+1=3"
   │
   │ Failed asserting that 2 is identical to 3.
   │
   │ /Users/ewout/proj/phpunit/tests/_files/DataproviderExecutionOrderTest.php:37
   │

 ✘ Add numbers with a dataprovider with data set "1+1=3"
   │
   │ Failed asserting that 2 is identical to 3.
   │
   │ /Users/ewout/proj/phpunit/tests/_files/DataproviderExecutionOrderTest.php:24
   │

FAILURES!
Tests: 8, Assertions: 8, Failures: 2.
