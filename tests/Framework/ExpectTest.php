<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @since      Class available since Release 2.0.0
 */
class Framework_ExpectTest extends PHPUnit_Framework_TestCase
{

    public function testExpectEquals()
    {
        try {
            $this->expectEquals(1, 2);
            $this->expectEquals(2, 3);
        } catch (Exception $e) {
            $this->fail("No exceptions should be thrown!!!");
        }
        $expectations = self::getFailedExpectations();
        self::resetFailedExpectations();
        $this->assertCount(2, $expectations);
        $this->expectEquals("Failed asserting that 2 matches expected 1.", $expectations[0]->toString());
        $this->expectEquals("Failed asserting that 3 matches expected 2.", $expectations[1]->toString());
    }

}
