--TEST--
Code coverage data is not appended when a code unit that was covered unintentionally cannot be reflected
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/test-runner/phpunit-class-that-is-not-loaded.xml';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/../../_files/delete_directory.php';

delete_directory(__DIR__ . '/_files/test-runner/.phpunit.cache.class-that-is-not-loaded');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s with FakeDriverForClassThatIsNotLoaded 1.0.0
Configuration: %s

Time: %s, Memory: %s

OK (1 test, 1 assertion)


Code Coverage Report:%w
  %s

 Summary:%w
  Classes:  0.00% (0/2)
  Methods:  0.00% (0/2)
  Lines:    0.00% (0/2)
