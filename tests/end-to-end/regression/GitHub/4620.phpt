--TEST--
https://github.com/sebastianbergmann/phpunit/issues/4620
--FILE--
<?php declare(strict_types=1);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/4620/bootstrap.php';
$_SERVER['argv'][] = __DIR__ . '/4620/Issue4620Test.php';

require_once __DIR__ . '/../../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Error in bootstrap script: PHPUnit\TestFixture\MyException:
Big boom. Big bada boom.
