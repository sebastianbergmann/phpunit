--TEST--
The configured code coverage driver class must be instantiable
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/code-coverage-driver/phpunit-class-is-not-instantiable.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/code-coverage-driver/.phpunit.cache.class-is-not-instantiable');
--EXPECTF--

An error occurred inside PHPUnit.

Message:  Configured code coverage driver class "PHPUnit\TestFixture\CodeCoverageDriver\AbstractCustomDriver" is not instantiable
Location: %s

#%d %A
