<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Column extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideColumnCount
     */
    public function testShouldAlwaysPass()
    {
        $this->assertTrue(true);
    }

    public function provideColumnCount()
    {
        $data = [];

        for ($i = 0; $i < 20; $i++) {
            $data[] = [];
        }

        return $data;
    }
}
