<?php

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Util;

use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\Command;

class HandleGlobalsTest extends TestCase
{
    /**
     * @var Handler
     */
    private static $command;
    private $arguments;
    private static $handleGlobalsMadePublic;

    public static function setUpBeforeClass()
    {
        self::$command = new Command;
        self::$handleGlobalsMadePublic = self::getMethod('handleGlobals');
    }

    protected function setUp()
    {
        // create array of global assignments as they would be set on the command line (using -g)
        $this->arguments['globals'] = [
            "myGlobalBool=true",
            "myGlobalInt=99",
            "myGlobalFloat=3.3",
            "myGlobalString=mysql:host=y;dbname=z",
            "myGlobalHash=['bar'=>'baz']",
            "myGlobalNull=null",
            "myGlobalEmpty="
        ];
    }

    public static function tearDownAfterClass()
    {
        unset(
            $GLOBALS['myGlobalBool'],
            $GLOBALS['myGlobalInt'],
            $GLOBALS['myGlobalFloat'],
            $GLOBALS['myGlobalString'],
            $GLOBALS['myGlobalHash'],
            $GLOBALS['myGlobalNull'],
            $GLOBALS['myGlobalEmpty']
        );
    }

    public function testGlobalBoolOption()
    {
        // call protected Commnd::handleGlobals() method
        //self::$command->handleGlobals($this->arguments['globals']);   // cannot simply call a protected method
        self::$handleGlobalsMadePublic->invokeArgs(self::$command, [$this->arguments['globals']]);  // workaround to call protected method

        $this->assertTrue($GLOBALS['myGlobalBool'], "Expected a global \$GLOBAL[myGlobalBool] to be defined");

        $this->assertEquals($GLOBALS['myGlobalInt'], 99, "Expected a global \$GLOBAL[myGlobalInt] to be defined");

        $this->assertEquals(round($GLOBALS['myGlobalFloat'], 1), round((float)3.3, 1), "Expected a global \$GLOBAL[myGlobalFloat] to be defined");

        $this->assertEquals($GLOBALS['myGlobalString'], "mysql:host=y;dbname=z", "Expected a global \$GLOBAL[myGlobalString] to be defined");

        $this->assertEquals($GLOBALS['myGlobalHash']["bar"], "baz", "Expected a global \$GLOBAL[myGlobalHash] to be defined");

        $this->assertNull($GLOBALS['myGlobalNull'], "Expected a global \$GLOBAL[myGlobalNull] to be defined");

        $this->assertEmpty($GLOBALS['myGlobalEmpty'], "Expected a global \$GLOBAL[myGlobalEmpty] to be defined");
    }

    /**
     * Workaround to call protected methods.
     *
     * See https://stackoverflow.com/questions/249664/best-practices-to-test-protected-methods-with-phpunit for details.
     *
     * @param string $name name of method in (hardcoded) Command class
     */
    protected static function getMethod($name)
    {
        $class = new \ReflectionClass('PHPUnit\TextUI\Command');    // hardcoded class
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
