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

use PHPUnit\Framework\TestCase;

class StringMatchesFormatDescriptionTest extends TestCase
{
    /**
     * @param bool   $expected
     * @param string $format
     * @param string $other
     * @dataProvider evaluateDataProvider
     */
    public function testEvaluate($expected, $format, $other): void
    {
        $constraint = new StringMatchesFormatDescription($format);

        $this->assertSame($expected, $constraint->evaluate($other, '', true));
    }

    public function evaluateDataProvider()
    {
        return [
            'Simple %e' => [
                true,
                '%e',
                DIRECTORY_SEPARATOR
            ],
            'Negative %e' => [
                false,
                '%e',
                'a'
            ],
            'Simple %s' => [
                true,
                '%s',
                'string'
            ],
            'Negative %s' => [
                false,
                '%s',
                "\n"
            ],
            'Simple %S' => [
                true,
                '%S',
                'string'
            ],
            'Negative %S' => [
                false,
                '%S',
                "1\n2\n2"
            ],
            'Simple %a' => [
                true,
                '%a',
                'string'
            ],
            'Negative %a' => [
                false,
                '%a',
                ''
            ],
            'Simple %A' => [
                true,
                '%A',
                'string'
            ],
            // Negative %A is not possible - it will match pretty much anything.
            'Simple %w' => [
                true,
                '%w',
                ' '
            ],
            'Negative %w' => [
                false,
                '%w',
                'nowhitespace'
            ],
            'Simple %i' => [
                true,
                '%i',
                '-10'
            ],
            'Negative %i' => [
                false,
                '%i',
                'abc'
            ],
            'Simple %d' => [
                true,
                '%d',
                '1'
            ],
            'Negative %d' => [
                false,
                '%d',
                'abc'
            ],
            'Simple %x' => [
                true,
                '%x',
                '0123456789abcdefABCDEF'
            ],
            'Negative %x' => [
                false,
                '%x',
                '_'
            ],
            'Simple %f' => [
                true,
                '%f',
                '-1.2e-10'
            ],
            'Negative %f' => [
                false,
                '%f',
                'foo'
            ],
            'Simple %c' => [
                true,
                '%c',
                'a'
            ],
            'Negative %c' => [
                false,
                '%c',
                'abc'
            ],
            'Escaped %' => [
                true,
                'Escaped %%e %%s %%S %%a %%A %%w %%i %%d %%x %%f %%c',
                'Escaped %e %s %S %a %A %w %i %d %x %f %c'
            ]
        ];
    }
}
