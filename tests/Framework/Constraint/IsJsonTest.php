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

class IsJsonTest extends ConstraintTestCase
{
    /**
     * @dataProvider evaluateDataprovider
     *
     * @param mixed $expected
     * @param mixed $jsonOther
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testEvaluate($expected, $jsonOther): void
    {
        $constraint = new IsJson;

        $this->assertEquals($expected, $constraint->evaluate($jsonOther, '', true));
    }

    public static function evaluateDataprovider()
    {
        return [
            'valid JSON'                                     => [true, '{}'],
            'empty string should be treated as invalid JSON' => [false, ''],
        ];
    }
}
