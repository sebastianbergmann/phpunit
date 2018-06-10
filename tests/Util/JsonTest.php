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

class JsonTest extends TestCase
{
    /**
     * @dataProvider canonicalizeProvider
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testCanonicalize($actual, $expected, $expectError): void
    {
        [$error, $canonicalized] = Json::canonicalize($actual);
        $this->assertEquals($expectError, $error);

        if (!$expectError) {
            $this->assertEquals($expected, $canonicalized);
        }
    }

    /**
     * @return array
     */
    public function canonicalizeProvider()
    {
        return [
            ['{"name":"John","age":"35"}', '{"age":"35","name":"John"}', false],
            ['{"name":"John","age":"35","kids":[{"name":"Petr","age":"5"}]}', '{"age":"35","kids":[{"age":"5","name":"Petr"}],"name":"John"}', false],
            ['"name":"John","age":"35"}', '{"age":"35","name":"John"}', true],
        ];
    }

    /**
     * @dataProvider prettifyProvider
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testPrettify($actual, $expected): void
    {
        $this->assertEquals($expected, Json::prettify($actual));
    }

    /**
     * @return array
     */
    public function prettifyProvider()
    {
        return [
            ['{"name":"John","age": "5"}', "{\n    \"name\": \"John\",\n    \"age\": \"5\"\n}"],
        ];
    }

    /**
     * @dataProvider prettifyExceptionProvider
     * @expectedException \PHPUnit\Framework\Exception
     * @expectedExceptionMessage Cannot prettify invalid json
     */
    public function testPrettifyException($json): void
    {
        Json::prettify($json);
    }

    /**
     * @return array
     */
    public function prettifyExceptionProvider()
    {
        return [
            ['"name":"John","age": "5"}'],
            [''],
        ];
    }
}
