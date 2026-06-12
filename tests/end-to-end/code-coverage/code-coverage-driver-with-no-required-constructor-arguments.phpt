--TEST--
A custom code coverage driver class whose constructor has no required arguments can be configured
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/code-coverage-driver/phpunit-with-no-required-constructor-arguments.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/code-coverage-driver/.phpunit.cache.with-no-required-constructor-arguments');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with CustomDriver 1.0.0
Configuration: %s

Time: %s, Memory: %s

OK (1 test, 1 assertion)


Code Coverage Report:%w
  %s

 Summary:%w
%A
