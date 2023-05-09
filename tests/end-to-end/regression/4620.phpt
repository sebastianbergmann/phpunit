--TEST--
https://github.com/sebastianbergmann/phpunit/issues/4620
https://github.com/sebastianbergmann/phpunit/issues/4877
--FILE--
<?php declare(strict_types=1);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/4620/bootstrap.php';
$_SERVER['argv'][] = __DIR__ . '/4620/Issue4620Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Error in bootstrap script: PHPUnit\TestFixture\MyException:
Big boom. Big bada boom.
%a

Previous error: Exception:
Previous boom.
%a
