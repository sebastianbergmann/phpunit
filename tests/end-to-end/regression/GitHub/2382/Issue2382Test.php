<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class Issue2382Test extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testOne($test): void
    {
        $this->assertInstanceOf(\Exception::class, $test);
    }

    public function dataProvider()
    {
        return [
            [
                $this->getMockBuilder(\Exception::class)->getMock(),
            ],
        ];
    }
}
