--TEST--
\PHPUnit\Framework\MockObject\Generator\Generator::generate('Foo', [], 'MockFoo', true)
--FILE--
<?php declare(strict_types=1);
class Foo
{
    public function __construct()
    {
    }
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator\Generator;

$mock = $generator->generate(
    'Foo',
    true,
    true,
    [],
    'MockFoo',
    true
);

print $mock->classCode();
--EXPECTF--
declare(strict_types=1);

class MockFoo extends Foo implements PHPUnit\Framework\MockObject\MockObjectInternal
{
    use PHPUnit\Framework\MockObject\%SStubApi;
    use PHPUnit\Framework\MockObject\MockObjectApi;
    use PHPUnit\Framework\MockObject\GeneratedAsMockObject;
    use PHPUnit\Framework\MockObject\Method;
    use PHPUnit\Framework\MockObject\DoubledCloneMethod;
}
