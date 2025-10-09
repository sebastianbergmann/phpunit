--TEST--
https://github.com/sebastianbergmann/phpunit/issues/6382
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/6382/Issue6382Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s


Fatal error: Declaration of Greg0ire\PhpunitReproducer\Child6382::__invoke() must be compatible with Greg0ire\PhpunitReproducer\Ancestor6382::__invoke(): void in %sChild.php on line %d
Fatal error: Premature end of PHP process when running Issue6382Test::testExample.
