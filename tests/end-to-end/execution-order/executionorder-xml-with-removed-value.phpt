--TEST--
Using a value that PHPUnit no longer supports for the executionOrder attribute is an error
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/fixture/xml-with-removed-value/phpunit.xml';
$_SERVER['argv'][] = '--do-not-record-test-run-history';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Cannot load XML configuration file %s: "no-depends" is no longer supported for the executionOrder attribute, use the resolveDependencies="false" XML configuration attribute instead
