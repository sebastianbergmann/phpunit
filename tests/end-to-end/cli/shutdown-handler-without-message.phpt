--TEST--
Shutdown Handler: exit('message')
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testWithoutMessage';
$_SERVER['argv'][] = __DIR__ . '/../_files/WithExitTest.php';

require __DIR__ . '/../../bootstrap.php';

// Destructs are called after register_shutdown_function callback
$x = new class ()
{
    function __destruct()
    {
        print '----';
    }
};

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

Fatal error: Premature end of PHP process when running PHPUnit\TestFixture\WithExitTest::testWithoutMessage.
----
