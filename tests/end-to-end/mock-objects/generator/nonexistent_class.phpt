--TEST--
\PHPUnit\Framework\MockObject\Generator\Generator::generate('NonExistentClass', [], 'MockFoo', true, true)
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator\Generator;

$mock = $generator->generate(
    'NonExistentClass',
    true,
    true,
    [],
    'MockFoo',
    true,
    true
);

print $mock->classCode();
--EXPECTF--
declare(strict_types=1);

class NonExistentClass
{
}

class MockFoo extends NonExistentClass implements PHPUnit\Framework\MockObject\MockObjectInternal
{
    use PHPUnit\Framework\MockObject\%SStubApi;
    use PHPUnit\Framework\MockObject\MockObjectApi;
    use PHPUnit\Framework\MockObject\GeneratedAsMockObject;
    use PHPUnit\Framework\MockObject\Method;
    use PHPUnit\Framework\MockObject\DoubledCloneMethod;
}
