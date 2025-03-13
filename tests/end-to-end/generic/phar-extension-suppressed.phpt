--TEST--
phpunit --configuration tests/_files/phpunit-example-extension --no-extensions
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/phpunit-example-extension';
$_SERVER['argv'][] = '--no-extensions';

require_once __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
Fatal error:%sTrait %sPHPUnit\ExampleExtension\TestCaseTrait%s not found in %A
