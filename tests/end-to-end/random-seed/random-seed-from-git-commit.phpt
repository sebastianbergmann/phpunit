--TEST--
--random-seed-from-git-commit seeds the random number generators with a seed that is derived from the Git commit that is checked out
--SKIPIF--
<?php declare(strict_types=1);
if (@shell_exec('git --version') === null) {
    print 'skip: Git is not available';
}
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../bootstrap.php';

$directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-random-seed-from-git-commit';

require __DIR__ . '/_files/git-repository.php';

// an empty commit whose hash does not depend on who makes it, and when
createGitRepositoryWithOneCommit($directory);

chdir($directory . DIRECTORY_SEPARATOR . 'repository');

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--random-seed-from-git-commit';
$_SERVER['argv'][] = __DIR__ . '/_files/RandomNumberTest.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
require __DIR__ . '/_files/git-repository.php';

removeDirectory(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit-random-seed-from-git-commit');
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       PHP %s
Random Seed:   1021567950564079878

setUpBeforeClass(): 505639062
testOne(): 192471227
testTwo(): 119865565
provider(): 1102033181
testThree(): 1641711136
Time: %s, Memory: %s

OK (3 tests, 3 assertions)
