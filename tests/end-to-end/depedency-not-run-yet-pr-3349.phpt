--TEST--
phpunit --reverse-order --ignore-dependencies ../_files/PR3349.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--debug';
$_SERVER['argv'][3] = '--verbose';
$_SERVER['argv'][4] = 'StackTest';
$_SERVER['argv'][5] = __DIR__ . '/../_files/PR3349.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

Test 'Test\PR3349\Tests::testSameClassDependencyCorrectOrderDependency' started
Test 'Test\PR3349\Tests::testSameClassDependencyCorrectOrderDependency' ended
Test 'Test\PR3349\Tests::testSameClassDependencyCorrectOrder' started
Test 'Test\PR3349\Tests::testSameClassDependencyCorrectOrder' ended
Test 'Test\PR3349\Tests::testSameClassDependencyReversedOrder' started
Test 'Test\PR3349\Tests::testSameClassDependencyReversedOrder' ended
Test 'Test\PR3349\Tests::testSameClassDependencyReversedOrderDependency' started
Test 'Test\PR3349\Tests::testSameClassDependencyReversedOrderDependency' ended
Test 'Test\PR3349\Tests::testExternalClassDependencyCorrectOrder' started
Test 'Test\PR3349\Tests::testExternalClassDependencyCorrectOrder' ended
Test 'Test\PR3349\Tests::testExternalClassDependencyFailingSetUp' started
Test 'Test\PR3349\Tests::testExternalClassDependencyFailingSetUp' ended
Test 'Test\PR3349\Tests::testExternalClassChainedMultipleDependencies' started
Test 'Test\PR3349\Tests::testExternalClassChainedMultipleDependencies' ended
Test 'Test\PR3349\Tests::testExternalClassDependencyFailed' started
Test 'Test\PR3349\Tests::testExternalClassDependencyFailed' ended
Test 'Test\PR3349\Tests::testExternalClassDependencySkipped' started
Test 'Test\PR3349\Tests::testExternalClassDependencySkipped' ended
Test 'Test\PR3349\Tests::testExternalClassChainedMultipleDependenciesFailureInChain' started
Test 'Test\PR3349\Tests::testExternalClassChainedMultipleDependenciesFailureInChain' ended
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSet_Dependency with data set #0 ('45348fed-cc85-4bdf-9c6b-3d132c951bd2', '45348fed-cc85-4bdf-9c6b-3d132...2-TEST')' started
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSet_Dependency with data set #0 ('45348fed-cc85-4bdf-9c6b-3d132c951bd2', '45348fed-cc85-4bdf-9c6b-3d132...2-TEST')' ended
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSet_Dependency with data set #1 ('bf62e6b7-1518-4a5b-9dbb-d803b52acf0a', 'bf62e6b7-1518-4a5b-9dbb-d803b...a-TEST')' started
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSet_Dependency with data set #1 ('bf62e6b7-1518-4a5b-9dbb-d803b52acf0a', 'bf62e6b7-1518-4a5b-9dbb-d803b...a-TEST')' ended
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSet_Dependency with data set #2 ('f1b7df75-d148-458a-9bce-278e6d9b2766', 'f1b7df75-d148-458a-9bce-278e6...6-TEST')' started
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSet_Dependency with data set #2 ('f1b7df75-d148-458a-9bce-278e6d9b2766', 'f1b7df75-d148-458a-9bce-278e6...6-TEST')' ended
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSet_Dependency with data set #3 ('c514d010-1cfc-49c4-9fc0-032fc6f5c6a0', 'c514d010-1cfc-49c4-9fc0-032fc...0-TEST')' started
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSet_Dependency with data set #3 ('c514d010-1cfc-49c4-9fc0-032fc6f5c6a0', 'c514d010-1cfc-49c4-9fc0-032fc...0-TEST')' ended
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSetWithoutUsingDatasetInTestExpectValueFromDependency' started
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSetWithoutUsingDatasetInTestExpectValueFromDependency' ended
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #0 ('45348fed-cc85-4bdf-9c6b-3d132...2-TEST')' started
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #0 ('45348fed-cc85-4bdf-9c6b-3d132...2-TEST')' ended
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #1 ('bf62e6b7-1518-4a5b-9dbb-d803b...a-TEST')' started
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #1 ('bf62e6b7-1518-4a5b-9dbb-d803b...a-TEST')' ended
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #2 ('f1b7df75-d148-458a-9bce-278e6...6-TEST')' started
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #2 ('f1b7df75-d148-458a-9bce-278e6...6-TEST')' ended
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #3 ('c514d010-1cfc-49c4-9fc0-032fc...0-TEST')' started
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #3 ('c514d010-1cfc-49c4-9fc0-032fc...0-TEST')' ended
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSetWithoutUsingDatasetInTestExpectValueFromString' started
Test 'Test\PR3349\Tests::testSameClassDependencyUsingDataSetWithoutUsingDatasetInTestExpectValueFromString' ended
Test 'Test\PR3349\Tests::testExternalClassDependencyUsingDataSetWithoutUsingDatasetInTestExpectValueFromDependency' started
Test 'Test\PR3349\Tests::testExternalClassDependencyUsingDataSetWithoutUsingDatasetInTestExpectValueFromDependency' ended
Test 'Test\PR3349\Tests::testExternalClassDependencyUsingDataSetWithoutUsingDatasetInTestExpectValueFromString' started
Test 'Test\PR3349\Tests::testExternalClassDependencyUsingDataSetWithoutUsingDatasetInTestExpectValueFromString' ended
Test 'Test\PR3349\Tests::testExternalClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #0 ('45348fed-cc85-4bdf-9c6b-3d132...3-TEST')' started
Test 'Test\PR3349\Tests::testExternalClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #0 ('45348fed-cc85-4bdf-9c6b-3d132...3-TEST')' ended
Test 'Test\PR3349\Tests::testExternalClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #1 ('bf62e6b7-1518-4a5b-9dbb-d803b...b-TEST')' started
Test 'Test\PR3349\Tests::testExternalClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #1 ('bf62e6b7-1518-4a5b-9dbb-d803b...b-TEST')' ended
Test 'Test\PR3349\Tests::testExternalClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #2 ('f1b7df75-d148-458a-9bce-278e6...7-TEST')' started
Test 'Test\PR3349\Tests::testExternalClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #2 ('f1b7df75-d148-458a-9bce-278e6...7-TEST')' ended
Test 'Test\PR3349\Tests::testExternalClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #3 ('c514d010-1cfc-49c4-9fc0-032fc...2-TEST')' started
Test 'Test\PR3349\Tests::testExternalClassDependencyUsingDataSetWithUsingDatasetInTestExpectValueFromDependency with data set #3 ('c514d010-1cfc-49c4-9fc0-032fc...2-TEST')' ended


Time: %s, Memory: %s

There were 5 skipped tests:

1) Test\PR3349\Tests::testSameClassDependencyReversedOrder
Reordering same class dependency function is not implemented. Please reorder "testSameClassDependencyReversedOrderDependency" before "testSameClassDependencyReversedOrder".

2) Test\PR3349\Tests::testExternalClassDependencyFailingSetUp
This test depends on "Test\PR3349\Test_FailingSetUp::testExternalClassDependencySuccessReturn100WithFailingSetUp" to pass.

3) Test\PR3349\Tests::testExternalClassDependencyFailed
This test depends on "Test\PR3349\Test_Dependencies::testExternalClassDependencyFailure" to pass.

4) Test\PR3349\Tests::testExternalClassDependencySkipped
This test depends on "Test\PR3349\Test_Dependencies::testExternalClassDependencySkipped" to pass.

5) Test\PR3349\Tests::testExternalClassChainedMultipleDependenciesFailureInChain
This test depends on "Test\PR3349\Test_ChainOne::testExternalClassDependencyChainFunctionOneWithFailureInChain" to pass.

OK, but incomplete, skipped, or risky tests!
Tests: 26, Assertions: 21, Skipped: 5.
