--TEST--
\PHPUnit\Framework\MockObject\Generator\Generator::generate('NS\Foo', [], 'MockFoo', true)
--FILE--
<?php declare(strict_types=1);
namespace NS;

interface IFoo
{
    public function __construct($bar);
}

class Foo implements IFoo
{
    public function __construct($bar)
    {
    }
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator\Generator;

$mock = $generator->generate(
    'NS\Foo',
    [],
    'MockFoo',
    true
);

print $mock->classCode();
--EXPECTF--
declare(strict_types=1);

class MockFoo extends NS\Foo implements PHPUnit\Framework\MockObject\MockObjectInternal
{
    use \PHPUnit\Framework\MockObject\StubApi;
    use \PHPUnit\Framework\MockObject\MockObjectApi;
    use \PHPUnit\Framework\MockObject\Method;
    use \PHPUnit\Framework\MockObject\MockedCloneMethod;
}
