--TEST--
The API for running examples of properties one at a time gives the same signature for the same failure in every process, whatever its working directory
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    print 'skip: Extension pcntl is required';
}
--FILE--
<?php declare(strict_types=1);
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../bootstrap.php';

// the working directory is not the directory of the configuration file
chdir(__DIR__);

$phpunit = PropertyRunner::boot(__DIR__ . '/_files/runner-api-signature/phpunit.xml');
$prefix  = 'PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi\SignatureTest::';

foreach (['testRaisesAnException', 'testRaisesAnAnonymousException', 'testRaisesATestDoubleOfAnException'] as $method) {
    $failure = $phpunit->replay($prefix . $method, [0], 1)['failure'];

    // the name of an anonymous class is shown up to the NUL byte that
    // precedes the path of the file in which it is declared
    $class = explode("\0", $failure['class'])[0];

    print $method . PHP_EOL;
    print '  signature: ' . $failure['signature'] . PHP_EOL;
    print '  class: ' . $class . PHP_EOL;
}
--EXPECTF--
testRaisesAnException
  signature: RuntimeException at tests%eSignatureTest.php:%d
  class: RuntimeException
testRaisesAnAnonymousException
  signature: RuntimeException at tests%eSignatureTest.php:%d
  class: RuntimeException@anonymous
testRaisesATestDoubleOfAnException
  signature: RuntimeException at tests%eSignatureTest.php:%d
  class: TestStub_RuntimeException_%x
