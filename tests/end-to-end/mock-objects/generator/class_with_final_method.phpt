--TEST--
\PHPUnit\Framework\MockObject\Generator\Generator::generate('ClassWithFinalMethod', [], 'MockFoo', true, true)
--FILE--
<?php declare(strict_types=1);
class ClassWithFinalMethod
{
    final public function finalMethod()
    {
    }
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator\Generator;

$mock = $generator->generate(
    type: 'ClassWithFinalMethod',
    mockObject: true,
    methods: [],
    mockClassName: 'MockFoo',
);

print $mock->classCode();
--EXPECT--
declare(strict_types=1);

class MockFoo extends ClassWithFinalMethod implements PHPUnit\Framework\MockObject\MockObjectInternal
{
    use PHPUnit\Framework\MockObject\StubApi;
    use PHPUnit\Framework\MockObject\MockObjectApi;
    use PHPUnit\Framework\MockObject\Method;
    use PHPUnit\Framework\MockObject\DoubledCloneMethod;
}
