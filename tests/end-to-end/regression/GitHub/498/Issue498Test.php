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

class Issue498Test extends TestCase
{
    /**
     * @test
     * @dataProvider shouldBeTrueDataProvider
     * @group falseOnly
     */
    public function shouldBeTrue($testData): void
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     * @dataProvider shouldBeFalseDataProvider
     * @group trueOnly
     */
    public function shouldBeFalse($testData): void
    {
        $this->assertFalse(false);
    }

    public function shouldBeTrueDataProvider()
    {

        //throw new Exception("Can't create the data");
        return [
            [true],
            [false],
        ];
    }

    public function shouldBeFalseDataProvider()
    {
        throw new Exception("Can't create the data");

        return [
            [true],
            [false],
        ];
    }
}
