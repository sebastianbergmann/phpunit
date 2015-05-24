<?php
class BeforeAndAfterTest extends PHPUnit_Framework_TestCase
{
    public static $beforeWasRun;
    public static $afterWasRun;
    private $somethingSetUpByBeforeMethod;
    public static $setUpWasRunAfterBeforeAnnotatedMethods;

    public static function resetProperties()
    {
        self::$beforeWasRun = 0;
        self::$afterWasRun = 0;
        self::$setUpWasRunAfterBeforeAnnotatedMethods = null;
    }

    /**
     * @before
     */
    public function initialSetup()
    {
        self::$beforeWasRun++;
        $this->somethingSetUpByBeforeMethod = true;
    }

    public function setUp()
    {
        if ($this->somethingSetUpByBeforeMethod) {
            self::$setUpWasRunAfterBeforeAnnotatedMethods = true;
        }
    }
    

    /**
     * @after
     */
    public function finalTeardown()
    {
        self::$afterWasRun++;
    }

    public function test1()
    {
    }
    public function test2()
    {
    }
}
