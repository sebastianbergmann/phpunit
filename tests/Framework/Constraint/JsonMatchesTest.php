<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Util\Json;

class JsonMatchesTest extends ConstraintTestCase
{
    /**
     * @dataProvider evaluateDataprovider
     *
     * @param mixed $expected
     * @param mixed $jsonOther
     * @param mixed $jsonValue
     *
     * @throws ExpectationFailedException
     * @throws \Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testEvaluate($expected, $jsonOther, $jsonValue): void
    {
        $constraint = new JsonMatches($jsonValue);

        $this->assertEquals($expected, $constraint->evaluate($jsonOther, '', true));
    }

    /**
     * @dataProvider evaluateThrowsExpectationFailedExceptionWhenJsonIsValidButDoesNotMatchDataprovider
     *
     * @param mixed $jsonOther
     * @param mixed $jsonValue
     *
     * @throws ExpectationFailedException
     * @throws \Exception
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testEvaluateThrowsExpectationFailedExceptionWhenJsonIsValidButDoesNotMatch($jsonOther, $jsonValue): void
    {
        $constraint = new JsonMatches($jsonValue);

        try {
            $constraint->evaluate($jsonOther, '', false);
            $this->fail(\sprintf('Expected %s to be thrown.', ExpectationFailedException::class));
        } catch (ExpectationFailedException $expectedException) {
            $comparisonFailure = $expectedException->getComparisonFailure();
            $this->assertNotNull($comparisonFailure);
            $this->assertSame(Json::prettify($jsonOther), $comparisonFailure->getActualAsString());
            $this->assertSame(Json::prettify($jsonValue), $comparisonFailure->getExpectedAsString());
            $this->assertSame('Failed asserting that two json values are equal.', $comparisonFailure->getMessage());
        }
    }

    public function testToString(): void
    {
        $jsonValue  = \json_encode(['Mascott' => 'Tux']);
        $constraint = new JsonMatches($jsonValue);

        $this->assertEquals('matches JSON string "' . $jsonValue . '"', $constraint->toString());
    }

    public static function evaluateDataprovider()
    {
        return [
            'valid JSON'                              => [true, \json_encode(['Mascott'                           => 'Tux']), \json_encode(['Mascott'                           => 'Tux'])],
            'error syntax'                            => [false, '{"Mascott"::}', \json_encode(['Mascott'         => 'Tux'])],
            'error UTF-8'                             => [false, \json_encode('\xB1\x31'), \json_encode(['Mascott' => 'Tux'])],
            'invalid JSON in class instantiation'     => [false, \json_encode(['Mascott'                          => 'Tux']), '{"Mascott"::}'],
            'string type not equals number'           => [false, '{"age": "5"}', '{"age": 5}'],
            'string type not equals boolean'          => [false, '{"age": "true"}', '{"age": true}'],
            'string type not equals null'             => [false, '{"age": "null"}', '{"age": null}'],
            'object fields are unordered'             => [true, '{"first":1, "second":"2"}', '{"second":"2", "first":1}'],
            'child object fields are unordered'       => [true, '{"Mascott": {"name":"Tux", "age":5}}', '{"Mascott": {"age":5, "name":"Tux"}}'],
            'null field different from missing field' => [false, '{"present": true, "missing": null}', '{"present": true}'],
            'array elements are ordered'              => [false, '["first", "second"]', '["second", "first"]'],
            'single boolean valid json'               => [true, 'true', 'true'],
            'single number valid json'                => [true, '5.3', '5.3'],
            'single null valid json'                  => [true, 'null', 'null'],
            'objects are not arrays'                  => [false, '{}', '[]']
        ];
    }

    public static function evaluateThrowsExpectationFailedExceptionWhenJsonIsValidButDoesNotMatchDataprovider()
    {
        return [
            'error UTF-8'                             => [\json_encode('\xB1\x31'), \json_encode(['Mascott' => 'Tux'])],
            'string type not equals number'           => ['{"age": "5"}', '{"age": 5}'],
            'string type not equals boolean'          => ['{"age": "true"}', '{"age": true}'],
            'string type not equals null'             => ['{"age": "null"}', '{"age": null}'],
            'null field different from missing field' => ['{"present": true, "missing": null}', '{"present": true}'],
            'array elements are ordered'              => ['["first", "second"]', '["second", "first"]']
        ];
    }
}
