--TEST--
TestDox: Default output; Data Provider with numeric data set name; TestDox metadata without placeholders; Colorized
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = '--colors=always';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderWithNumericDataSetNameAndMetadataTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Time: %s, Memory: %s

[4mText from class-level TestDox metadata[0m
[32m ✔ [0mText from method-level TestDox metadata for successful test[2m with data set [22m[36m0[0m
[31m ✘ [0mText from method-level TestDox metadata for failing test[2m with data set [22m[36m0[0m
   [31m┐[0m
   [31m├[0m [41;37mFailed asserting that false is true.[0m
   [31m│[0m
   [31m│[0m %s[22m_files[2m%e[22mDataProviderWithNumericDataSetNameAndMetadataTest.php[2m:[22m[34m%d[0m
   [31m┴[0m

[37;41mFAILURES![0m
[37;41mTests: 2[0m[37;41m, Assertions: 2[0m[37;41m, Failures: 1[0m[37;41m.[0m
