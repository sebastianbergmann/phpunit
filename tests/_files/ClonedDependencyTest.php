<?php
use PHPUnit\Framework\TestCase;

class ClassWithUnclonableProperty {
    public $cloned = false;
    protected $unclonableProperty;

    public function __construct()
    {
        $this->unclonableProperty = new class {
            private function __clone() {}
        };
    }

    public function __clone()
    {
        $this->cloned = true;
    }
}

class ClonedDependencyTest extends TestCase
{
    private static $dependency;

    public static function setUpBeforeClass()
    {
        self::$dependency = new stdClass;
    }

    public function testOne()
    {
        $this->assertTrue(true);

        return self::$dependency;
    }

    /**
     * @depends testOne
     */
    public function testTwo($dependency)
    {
        $this->assertSame(self::$dependency, $dependency);
    }

    /**
     * @depends !clone testOne
     */
    public function testThree($dependency)
    {
        $this->assertSame(self::$dependency, $dependency);
    }

    /**
     * @depends clone testOne
     */
    public function testFour($dependency)
    {
        $this->assertNotSame(self::$dependency, $dependency);
    }

    public function testFive()
    {
        $this->assertTrue(true);

        return new ClassWithUnclonableProperty;
    }

    /**
     * @depends clone testFive
     */
    public function testSix(ClassWithUnclonableProperty $dependency)
    {
        $this->assertTrue($dependency->cloned);
    }

    /**
     * Replaces deepClone mechanism for ClassWithUnclonableProperty with shallow copy
     */
    protected function retrieveClonedDependencyValue($dependency)
    {
        if ($dependency instanceof ClassWithUnclonableProperty) {
            return clone $dependency;
        }

        return parent::retrieveClonedDependencyValue($dependency);
    }
}
