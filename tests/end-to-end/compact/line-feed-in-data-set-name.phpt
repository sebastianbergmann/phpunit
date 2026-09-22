--TEST--
Line feeds in the name of a test are made visible in the header of a record in the compact output
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--compact';
$_SERVER['argv'][] = __DIR__ . '/../_files/LineFeedInDataSetNameTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s


--- FAILURE: PHPUnit\TestFixture\LineFeedInDataSetNameTest::testDataSetName@data set name\u{000A}--- FAILURE: Forged::testForged\u{000A}forged body\u{000D}\u{000A}more with data (true)
Failed asserting that true is false.

%sLineFeedInDataSetNameTest.php:%d

FAILURES (1 test, 1 assertion, 1 failure)
