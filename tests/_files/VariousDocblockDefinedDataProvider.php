<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
final class VariousDocblockDefinedDataProvider
{
    /**
     * @anotherAnnotation
     */
    public function anotherAnnotation(): void
    {
    }

    /**
     * @testWith [1]
     */
    public function testWith1(): void
    {
    }

    /**
     * @testWith [1, 2]
     * [3, 4]
     */
    public function testWith1234(): void
    {
    }

    /**
     * @testWith ["ab"]
     * [true]
     * [null]
     */
    public function testWithABTrueNull(): void
    {
    }

    /**
     * @testWith [1]
     *           [2]
     * @annotation
     */
    public function testWith12AndAnotherAnnotation(): void
    {
    }

    /**
     * @testWith [1]
     *           [2]
     * blah blah
     */
    public function testWith12AndBlahBlah(): void
    {
    }

    /**
     * @testWith ["\"", "\""]
     */
    public function testWithEscapedString(): void
    {
    }

    /**
     * @testWith [s]
     */
    public function testWithMalformedValue(): void
    {
    }

    /**
     * @testWith ["valid"]
     *           [invalid]
     */
    public function testWithWellFormedAndMalformedValue(): void
    {
    }
}
