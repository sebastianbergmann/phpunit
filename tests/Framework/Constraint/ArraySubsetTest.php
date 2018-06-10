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

class ArraySubsetTest extends ConstraintTestCase
{
    public static function evaluateDataProvider()
    {
        return [
            'loose associative array subset and array other' => [
                'expected' => true,
                'subset'   => ['bar' => 0],
                'other'    => ['foo' => '', 'bar' => '0'],
                'strict'   => false
            ],
            'strict associative array subset and array other' => [
                'expected' => false,
                'subset'   => ['bar' => 0],
                'other'    => ['foo' => '', 'bar' => '0'],
                'strict'   => true
            ],
            'loose associative array subset and ArrayObject other' => [
                'expected' => true,
                'subset'   => ['bar' => 0],
                'other'    => new \ArrayObject(['foo' => '', 'bar' => '0']),
                'strict'   => false
            ],
            'strict associative ArrayObject subset and array other' => [
                'expected' => true,
                'subset'   => new \ArrayObject(['bar' => 0]),
                'other'    => ['foo' => '', 'bar' => 0],
                'strict'   => true
            ],
            'loose indexed array subset and array other' => [
                'expected' => true,
                'subset'   => [0],
                'other'    => ['', '0'],
                'strict'   => false
            ],
            'strict indexed array subset and array other' => [
                'expected' => false,
                'subset'   => [0],
                'other'    => ['', '0'],
                'strict'   => true
            ],
            'loose indexed array subset and ArrayObject other' => [
                'expected' => true,
                'subset'   => [0],
                'other'    => new \ArrayObject(['', '0']),
                'strict'   => false
            ],
            'strict indexed ArrayObject subset and array other' => [
                'expected' => true,
                'subset'   => new \ArrayObject([0]),
                'other'    => ['', 0],
                'strict'   => true
            ],
            'loose unordered indexed array subset and array other' => [
                'expected' => true,
                'subset'   => [0, '1'],
                'other'    => ['1', '2', '0'],
                'strict'   => false
            ],
            'strict unordered indexed array subset and array other' => [
                'expected' => false,
                'subset'   => [0, '1'],
                'other'    => ['1', '2', '0'],
                'strict'   => true
            ],
            'loose unordered indexed array subset and ArrayObject other' => [
                'expected' => true,
                'subset'   => [0, '1'],
                'other'    => new \ArrayObject(['1', '2', '0']),
                'strict'   => false
            ],
            'strict unordered indexed ArrayObject subset and array other' => [
                'expected' => true,
                'subset'   => new \ArrayObject([0, '1']),
                'other'    => ['1', '2', 0],
                'strict'   => true
            ],
            'loose unordered multidimensional indexed array subset and array other' => [
                'expected' => true,
                'subset'   => [
                    [[3, 4], 2],
                    '10',
                ],
                'other'    => [
                    0   => '1',
                    'a' => [
                        'aa' => '2',
                        'ab' => [5, 4, 3],
                        'ac' => 10,
                    ],
                    'b' => '10',
                ],
                'strict'   => false
            ],
            'strict unordered multidimensional indexed array subset and array other' => [
                'expected' => false,
                'subset'   => [
                    [[3, 4], 2],
                    '10',
                ],
                'other'    => [
                    0   => '1',
                    'a' => [
                        'aa' => '2',
                        'ab' => [5, 4, 3],
                        'ac' => 10,
                    ],
                    'b' => '10',
                ],
                'strict'   => true
            ],
            'loose unordered multidimensional indexed array subset and ArrayObject other' => [
                'expected' => true,
                'subset'   => [
                    [[3, 4], 2],
                    '10',
                ],
                'other'    => new \ArrayObject([
                    0   => '1',
                    'a' => [
                        'aa' => '2',
                        'ab' => [5, 4, 3],
                        'ac' => 10,
                    ],
                    'b' => '10',
                ]),
                'strict'   => false
            ],
            'strict unordered multidimensional indexed ArrayObject subset and array other' => [
                'expected' => true,
                'subset'   => new \ArrayObject([
                    [[3, 4], '2'],
                    '10',
                ]),
                'other'    => [
                    0   => '1',
                    'a' => [
                        'aa' => '2',
                        'ab' => [5, 4, 3],
                        'ac' => 10,
                    ],
                    'b' => '10',
                ],
                'strict'   => true
            ],
        ];
    }

    /**
     * @param bool               $expected
     * @param array|\Traversable $subset
     * @param array|\Traversable $other
     * @param bool               $strict
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @dataProvider evaluateDataProvider
     */
    public function testEvaluate($expected, $subset, $other, $strict): void
    {
        $constraint = new ArraySubset($subset, $strict);

        $this->assertSame($expected, $constraint->evaluate($other, '', true));
    }

    public function testEvaluateWithArrayAccess(): void
    {
        $arrayAccess = new \ArrayAccessible(['foo' => 'bar']);

        $constraint = new ArraySubset(['foo' => 'bar']);

        $this->assertTrue($constraint->evaluate($arrayAccess, '', true));
    }

    public function testEvaluateFailMessage(): void
    {
        $constraint = new ArraySubset(['foo' => 'bar']);

        try {
            $constraint->evaluate(['baz' => 'bar'], '', false);
            $this->fail(\sprintf('Expected %s to be thrown.', ExpectationFailedException::class));
        } catch (ExpectationFailedException $expectedException) {
            $comparisonFailure = $expectedException->getComparisonFailure();
            $this->assertNotNull($comparisonFailure);
            $this->assertContains('[foo] => bar', $comparisonFailure->getExpectedAsString());
            $this->assertContains('[baz] => bar', $comparisonFailure->getActualAsString());
        }
    }
}
