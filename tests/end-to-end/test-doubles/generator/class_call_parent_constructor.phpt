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
    type: 'Foo',
    mockObject: true,
    methods: [],
    mockClassName: 'MockFoo',
);

print $mock->classCode();
--EXPECT--
declare(strict_types=1);

class MockFoo extends Foo implements PHPUnit\Framework\MockObject\MockObjectInternal
{
    use PHPUnit\Framework\MockObject\StubApi;
    use PHPUnit\Framework\MockObject\MockObjectApi;
    use PHPUnit\Framework\MockObject\Method;
    use PHPUnit\Framework\MockObject\DoubledCloneMethod;
}
