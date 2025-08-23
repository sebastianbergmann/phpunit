--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6304
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--display-phpunit-deprecations';
$_SERVER['argv'][] = __DIR__ . '/6304/Issue6304MethodMetadataTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s MB

There was 1 PHPUnit test runner deprecation:

1) Metadata found in doc-comment for method Issue6304MethodMetadataTest::testOne(). Metadata in doc-comments is deprecated and will no longer be supported in PHPUnit 12. Update your test code to use attributes instead.

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Deprecations: 1.
