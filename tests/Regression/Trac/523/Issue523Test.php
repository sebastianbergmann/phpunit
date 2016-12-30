<?php
use PHPUnit\Framework\TestCase;

class Issue523Test extends TestCase
{
    public function testAttributeEquals()
    {
        $this->assertAttributeEquals('foo', 'field', new Issue523());
    }
};

class Issue523 extends ArrayIterator
{
    protected $field = 'foo';
}
