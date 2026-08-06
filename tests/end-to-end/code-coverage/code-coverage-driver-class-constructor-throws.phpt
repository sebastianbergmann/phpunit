--TEST--
The crash message shows the previous throwable when the configured code coverage driver class cannot be instantiated
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/code-coverage-driver-that-throws/phpunit.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/code-coverage-driver-that-throws/.phpunit.cache.driver-that-throws');
--EXPECTF--

An error occurred inside PHPUnit.

Message:  (no message)
Location: %sDriverThatThrows.php:%d

#%d %A
