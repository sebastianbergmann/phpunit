--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6356
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/InvalidVersionConstraintNoVersionTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
An error occurred inside PHPUnit.

Message:  (no message)
Location: %s%eRequirement.php:%d

%a
