--TEST--
The configured code coverage driver class must extend SebastianBergmann\CodeCoverage\Driver\Driver
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/code-coverage-driver/phpunit-class-does-not-extend-driver.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/code-coverage-driver/.phpunit.cache.class-does-not-extend-driver');
--EXPECTF--

An error occurred inside PHPUnit.

Message:  Configured code coverage driver class "PHPUnit\TestFixture\CodeCoverageDriver\NotADriver" does not extend SebastianBergmann\CodeCoverage\Driver\Driver
Location: %s

#%d %A
