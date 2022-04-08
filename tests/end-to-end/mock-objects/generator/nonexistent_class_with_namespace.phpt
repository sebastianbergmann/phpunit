--TEST--
\PHPUnit\Framework\MockObject\Generator::generate('Foo', [], 'MockFoo', true, true)
--FILE--
<?php declare(strict_types=1);
require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->generate(
    'NS\Foo',
    [],
    'MockFoo',
    true,
    true
);

print $mock->getClassCode();
--EXPECTF--
declare(strict_types=1);

namespace NS {

class Foo
{
}

}

namespace {

class MockFoo extends NS\Foo implements PHPUnit\Framework\MockObject\MockObject
{
    use \PHPUnit\Framework\MockObject\Api;
    use \PHPUnit\Framework\MockObject\Method;
    use \PHPUnit\Framework\MockObject\MockedCloneMethod;
}

}
