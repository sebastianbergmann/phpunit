--TEST--
requireCoverageMetadataOnMediumTests="true" makes a medium test without code coverage target metadata risky
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/require-coverage-metadata/medium.xml';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Configuration: %s

.R..                                                                4 / 4 (100%)

Time: %s, Memory: %s

There was 1 risky test:

1) PHPUnit\TestFixture\RequireCoverageMetadata\MediumTest::testOne
This test does not define a code coverage target but is expected to do so

%sMediumTest.php:%d

OK, but there were issues!
Tests: 4, Assertions: 4, Risky: 1.
