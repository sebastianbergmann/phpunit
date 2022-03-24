--TEST--
\PHPUnit\Framework\MockObject\Generator::generate('Foo', [], 'MockFoo', true, true)
--SKIPIF--
<?php declare(strict_types=1);
if (PHP_MAJOR_VERSION < 8) {
    print 'skip: PHP 8 is required.';
}
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
    use \PHPUnit\Framework\MockObject\MockedCloneMethodWithVoidReturnType;
}

}
