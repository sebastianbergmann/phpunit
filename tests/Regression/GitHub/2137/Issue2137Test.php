<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Issue2137Test extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideBrandService
     *
     * @param mixed $provided
     * @param mixed $expected
     *
     * @throws Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testBrandService($provided, $expected): void
    {
        $this->assertSame($provided, $expected);
    }

    public function provideBrandService()
    {
        return [
            //[true, true]
            new stdClass() // not valid
        ];
    }

    /**
     * @dataProvider provideBrandService
     *
     * @param mixed $provided
     * @param mixed $expected
     *
     * @throws Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testSomethingElseInvalid($provided, $expected): void
    {
        $this->assertSame($provided, $expected);
    }
}
