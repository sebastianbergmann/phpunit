<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\TestCase;

class IsJsonTest extends TestCase
{
    /**
     * @dataProvider evaluateDataprovider
     */
    public function testEvaluate($expected, $jsonOther)
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
