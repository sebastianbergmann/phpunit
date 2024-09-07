--TEST--
https://github.com/sebastianbergmann/phpunit/issues/4625
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/4625';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--SKIPIF--
<?php declare(strict_types=1);
if (version_compare('8.3.0-dev', PHP_VERSION, '>')) {
    print 'skip: PHP 8.3 is required (due to different message).'; 
}
--EXPECTF--
An error occurred inside PHPUnit.

Message:  Cannot access offset of type array on array
Location: %sDataProvider.php:%d

#0 %sDataProvider.php(%d): PHPUnit\Metadata\Api\DataProvider->dataProvidedByMethods()
#1 %sTestBuilder.php(%d): PHPUnit\Metadata\Api\DataProvider->providedData()
#2 %sTestSuite.php(%d): PHPUnit\Framework\TestBuilder->build()
#3 %sTestSuite.php(%d): PHPUnit\Framework\TestSuite->addTestMethod()
#4 %sTestSuite.php(%d): PHPUnit\Framework\TestSuite::fromClassReflector()
#5 %sTestSuite.php(%d): PHPUnit\Framework\TestSuite->addTestSuite()
#6 %sTestSuite.php(%d): PHPUnit\Framework\TestSuite->addTestFile()
#7 %sTestSuiteBuilder.php(%d): PHPUnit\Framework\TestSuite->addTestFiles()
#8 %sTestSuiteBuilder.php(%d): PHPUnit\TextUI\Configuration\TestSuiteBuilder->testSuiteFromPath()
#9 %sApplication.php(%d): PHPUnit\TextUI\Configuration\TestSuiteBuilder->build()
#10 %sApplication.php(%d): PHPUnit\TextUI\Application->buildTestSuite()
#11 Standard input code(%d): PHPUnit\TextUI\Application->run()
#12 {main}
