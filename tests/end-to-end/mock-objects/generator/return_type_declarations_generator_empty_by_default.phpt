--TEST--
Iterable return types should return empty array by default
--SKIPIF--
<?php if(str_contains((string)ini_get('xdebug.mode'), 'develop')) {
print 'skip: xdebug.mode=develop is enabled';
}
--FILE--
<?php declare(strict_types=1);
interface Foo
{
    public function forTraversable(): traversable;
    public function forGenerator(): Generator;
    public function forIterable(): iterable;
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator\Generator;

$mock = $generator->testDouble('Foo', false, false);

var_dump(iterator_to_array($mock->forTraversable()));
var_dump(iterator_to_array($mock->forGenerator()));
var_dump(iterator_to_array($mock->forIterable()));
--EXPECTF--
array(0) {
}
array(0) {
}
array(0) {
}
