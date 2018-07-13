--TEST--
Support --loader option.
--FILE--
<?php
require_once __DIR__ . '/../bootstrap.php';
$root = \org\bovigo\vfs\vfsStream::setup('root');

class StubLoader implements \PHPUnit\Runner\TestSuiteLoader
{
    public function load(string $suiteClassName, string $suiteClassFile = ''): ReflectionClass
    {
        throw new \PHPUnit\Framework\AssertionFailedError('Method ' . __METHOD__ . ' not implemented yet.');
    }

    public function reload(ReflectionClass $aClass): ReflectionClass
    {
        throw new \PHPUnit\Framework\AssertionFailedError('Method ' . __METHOD__ . ' not implemented yet.');
    }
}
$_SERVER['argv'][] = ''; // present to start index at 0
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--loader';
$_SERVER['argv'][] = StubLoader::class;
$_SERVER['argv'][] = __DIR__ . '/_files/AlwaysPass.php';

PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
Method StubLoader::load not implemented yet.
