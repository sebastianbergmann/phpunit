--TEST--
Iterable return types should return empty array by default
--FILE--
<?php declare(strict_types=1);
interface Foo
{
    public function forTraversable(): traversable;
    public function forGenerator(): Generator;
    public function forIterable(): iterable;
}

require_once __DIR__ . '/../../../bootstrap.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

$mock = $generator->getMock('Foo');

print count(iterator_to_array($mock->forTraversable())) . PHP_EOL;
print count(iterator_to_array($mock->forGenerator())) . PHP_EOL;
print count(iterator_to_array($mock->forIterable())) . PHP_EOL;
--EXPECTF--
0
0
0
