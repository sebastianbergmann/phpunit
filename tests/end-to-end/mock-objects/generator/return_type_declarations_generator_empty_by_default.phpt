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

print count(iterator_to_array($mock->forTraversable())) . PHP_EOL;
print count(iterator_to_array($mock->forGenerator())) . PHP_EOL;
print count(iterator_to_array($mock->forIterable())) . PHP_EOL;
--EXPECTF--
0
0
0
