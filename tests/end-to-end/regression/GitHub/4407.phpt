--TEST--
https://github.com/sebastianbergmann/phpunit/issues/4407
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/4407/Issue4407Test.php';

require_once __DIR__ . '/../../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

F                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 failure:

1) Issue4407Test::testOne
Failed asserting that two DOM documents are equal.
--- Expected
+++ Actual
@@ @@
 <?xml version="1.0"?>
-<root>
-  <child>text</child>
-</root>

%sIssue4407Test.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
