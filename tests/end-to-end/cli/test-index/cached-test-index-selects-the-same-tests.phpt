--TEST--
The test index does not change which tests are selected
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/_files/setup.php';

$directory = setUpTestFiles();

// The first run has nothing to go on and has to load every test file
$cold = listTests($directory, 'a');

print is_file($directory . '/cache/test-index') ? "index written\n" : "index not written\n";

// The second run knows that TwoTest.php cannot contribute a test to it
$warm = listTests($directory, 'a');

print $cold === $warm ? "cold and warm run select the same tests\n" : "SELECTION DIFFERS\n" . $cold . "---\n" . $warm;
print $cold;

tearDownTestFiles($directory);
--EXPECTF--
index written
cold and warm run select the same tests
 - PHPUnit\TestFixture\TestIndex\OneTest::testInGroupa
