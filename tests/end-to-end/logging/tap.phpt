--TEST--
TAP
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--tap';
$_SERVER['argv'][] = __DIR__ . '/../_files/OutcomesAndIssuesTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
TAP version 14

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithoutIssues
ok 1 - PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithoutIssues

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithRisky
ok 2 - PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithRisky

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithDeprecation
ok 3 - PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithDeprecation

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithNotice
ok 4 - PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithNotice

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithWarning
ok 5 - PHPUnit\TestFixture\OutcomesAndIssuesTest::testSuccessWithWarning

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithDeprecation
not ok 6 - PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithDeprecation

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithNotice
not ok 7 - PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithNotice

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithWarning
not ok 8 - PHPUnit\TestFixture\OutcomesAndIssuesTest::testFailWithWarning

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithDeprecation
not ok 9 - PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithDeprecation

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithNotice
not ok 10 - PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithNotice

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithWarning
not ok 11 - PHPUnit\TestFixture\OutcomesAndIssuesTest::testErrorWithWarning

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testIncompleteWithDeprecation

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testIncompleteWithNotice

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testIncompleteWithWarning

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testSkippedWithDeprecation

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testSkippedWithNotice

# successfully prepared PHPUnit\TestFixture\OutcomesAndIssuesTest::testSkippedWithWarning

1..17
