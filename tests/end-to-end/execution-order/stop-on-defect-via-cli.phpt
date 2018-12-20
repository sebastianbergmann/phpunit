--TEST--
phpunit --stop-on-defect StopOnWarningTestSuite ./tests/_files/StopOnWarningTestSuite.php
--FILE--
<?php
$arguments = [
    '--no-configuration',
    '--stop-on-defect',
    'StopOnWarningTestSuite',
    \realpath(__DIR__ . '/../../_files/StopOnWarningTestSuite.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

W

Time: %s, Memory: %s

There was 1 warning:

1) Warning
No tests found in class "NoTestCases".

WARNINGS!
Tests: 1, Assertions: 0, Warnings: 1.
