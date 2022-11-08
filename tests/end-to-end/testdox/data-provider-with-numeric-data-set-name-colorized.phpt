--TEST--
TestDox: Default output; Data Provider with numeric data set name; No TestDox metadata; Colorized
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=always';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderWithNumericDataSetNameTest.php';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

[4mData Provider With Numeric Data Set Name (PHPUnit\TestFixture\TestDox\DataProviderWithNumericDataSetName)[0m
 [32m✔[0m Something that works[2m with data set [22m[36m0[0m
 [31m✘[0m Something that does not work[2m with data set [22m[36m0[0m
   [31m┐[0m
   [31m├[0m [41;37mFailed asserting that false is true.[0m
   [31m│[0m
   [31m╵[0m %stests[2m/[22mend-to-end[2m/[22mtestdox[2m/[22m_files[2m/[22mDataProviderWithNumericDataSetNameTest.php[2m:[22m[34m%d[0m
   [31m┴[0m

Time: %s, Memory: %s

Summary of non-successful tests:

[4mData Provider With Numeric Data Set Name (PHPUnit\TestFixture\TestDox\DataProviderWithNumericDataSetName)[0m
 [31m✘[0m Something that does not work[2m with data set [22m[36m0[0m
   [31m┐[0m
   [31m├[0m [41;37mFailed asserting that false is true.[0m
   [31m│[0m
   [31m╵[0m %stests[2m/[22mend-to-end[2m/[22mtestdox[2m/[22m_files[2m/[22mDataProviderWithNumericDataSetNameTest.php[2m:[22m[34m%d[0m
   [31m┴[0m

[37;41mFAILURES![0m
[37;41mTests: 2[0m[37;41m, Assertions: 2[0m[37;41m, Failures: 1[0m[37;41m.[0m
