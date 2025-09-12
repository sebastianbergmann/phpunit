--TEST--
--FILE--
<?php
$phpunit = __DIR__ . '/../../../../phpunit';
passthru(\sprintf('php %s --do-not-cache-result --no-configuration %s/WithExitTest.php --filter testWithMessage',$phpunit, __DIR__,));
echo "\n------\n";
passthru(\sprintf('php %s --do-not-cache-result --no-configuration %s/WithExitTest.php --filter testWithoutMessage',$phpunit, __DIR__,));
echo "\n------\n";
echo PHP_EOL;
--EXPECTF--
%s by Sebastian Bergmann and contributors.

Runtime: %s

My Custom Exit MessageFatal error: Premature end of PHP process when running PHPUnit\TestFixture\WithExitTest::testWithMessage.

------
%s by Sebastian Bergmann and contributors.

Runtime: %s

Fatal error: Premature end of PHP process when running PHPUnit\TestFixture\WithExitTest::testWithoutMessage.

------

