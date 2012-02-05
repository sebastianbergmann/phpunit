<?php

/**
 * @package    PHPUnit
 * @author     Andrew Lawson <http://adlawson.com>
 * @copyright  2001-2012 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 */
class Issue487Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test render method (worked fine before)
     * @expectedException PHPUnit_Framework_Error_Warning
     * @return void
     */
    public function testRender()
    {
        $renderer = new Issue487TestRenderer;
        $renderer->render();
    }

    /**
     * Test __toString method (didn't work before this patch)
     * @expectedException PHPUnit_Framework_Error_Warning
     * @return void
     */
    public function testToString()
    {
        $renderer = new Issue487TestRenderer;
        $renderer->__toString();
    }
}

/**
 * Mock renderer
 */
class Issue487TestRenderer
{
    public function render()
    {
        trigger_error('User error: Replace user.', E_USER_WARNING);
    }

    public function __toString()
    {
        trigger_error('User error: Replace user.', E_USER_WARNING);
    }
}