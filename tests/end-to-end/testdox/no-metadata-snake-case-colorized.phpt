--TEST--
TestDox: Default output; Test name in snake-case notation; No TestDox metadata; Colorized
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=always';
$_SERVER['argv'][] = __DIR__ . '/_files/SnakeCaseTest.php';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

[4mSnake Case (PHPUnit\TestFixture\TestDox\SnakeCase)[0m
 [32m✔[0m Something that works
 [31m✘[0m Something that does not work
   [31m┐[0m
   [31m├[0m [41;37mFailed asserting that false is true.[0m
   [31m│[0m
   [31m╵[0m %stests[2m%e[22mend-to-end[2m%e[22mtestdox[2m%e[22m_files[2m%e[22mSnakeCaseTest.php[2m:[22m[34m%d[0m
   [31m┴[0m

Time: %s, Memory: %s

Summary of non-successful tests:

[4mSnake Case (PHPUnit\TestFixture\TestDox\SnakeCase)[0m
 [31m✘[0m Something that does not work
   [31m┐[0m
   [31m├[0m [41;37mFailed asserting that false is true.[0m
   [31m│[0m
   [31m╵[0m %stests[2m%e[22mend-to-end[2m%e[22mtestdox[2m%e[22m_files[2m%e[22mSnakeCaseTest.php[2m:[22m[34m%d[0m
   [31m┴[0m

[37;41mFAILURES![0m
[37;41mTests: 2[0m[37;41m, Assertions: 2[0m[37;41m, Failures: 1[0m[37;41m.[0m
