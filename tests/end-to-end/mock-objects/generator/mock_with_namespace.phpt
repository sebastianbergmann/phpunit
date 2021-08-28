--FILE--
<?php declare(strict_types=1);
namespace Bar\Test {
    class Bar {
        public function something(Bar $bar) {}
    }
}

namespace {
require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->generate(
    Bar::class,
    null,
    'Some\\Mock\\Class',
);

print $mock->getClassCode();
}
--EXPECT--
declare(strict_types=1);

namespace Some\Mock {

class Class extends \Bar implements \PHPUnit\Framework\MockObject\MockObject
{
    use \PHPUnit\Framework\MockObject\Api;
    use \PHPUnit\Framework\MockObject\Method;
    use \PHPUnit\Framework\MockObject\MockedCloneMethod;
}

}
