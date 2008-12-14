<?php
class ParentClassWithPrivateAttributes
{
    protected static $privateStaticParentAttribute = 'parent';
    protected $privateParentAttribute = 'parent';
}

class ClassWithNonPublicAttributes extends ParentClassWithPrivateAttributes
{
    public static $publicStaticAttribute = 'foo';
    protected static $protectedStaticAttribute = 'bar';
    protected static $privateStaticAttribute = 'baz';

    public $publicAttribute = 'foo';
    public $foo = 1;
    public $bar = 2;
    protected $protectedAttribute = 'bar';
    protected $privateAttribute = 'baz';

    public $publicArray = array('foo');
    protected $protectedArray = array('bar');
    protected $privateArray = array('baz');
}
?>
