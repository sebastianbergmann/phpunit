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
Tests: 10, Assertions: 5, Skipped: 5.
