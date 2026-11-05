--TEST--
--random-seed-from-git-commit is ignored with a warning when no Git commit is checked out in the working directory
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/_files/git-repository.php';

$directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-random-seed-from-git-commit-without-commit';

removeDirectory($directory);
mkdir($directory);

// Git does not look for a repository in the directories above
putenv('GIT_DIR');
putenv('GIT_CEILING_DIRECTORIES=' . dirname($directory));

chdir($directory);

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--random-seed-from-git-commit';
$_SERVER['argv'][] = __DIR__ . '/_files/RandomNumberTest.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/_files/git-repository.php';

removeDirectory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-random-seed-from-git-commit-without-commit');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s

setUpBeforeClass(): %d
testOne(): %d
testTwo(): %d
provider(): %d
testThree(): %d
Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Option "--random-seed-from-git-commit" ignored because no Git commit is checked out in the working directory

OK, but there were issues!
Tests: 3, Assertions: 3, PHPUnit Warnings: 1.
