<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Annotation;

use PHPUnit\Framework\TestCase;

final class TestWithTest extends TestCase
{
    /**
     * @testWith [1, 2, 3]
     */
    public function testDataSetIsValidJson(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @testWith [1, 2, 3}
     */
    public function testDataSetIsInvalidJson(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @testWith ...
     */
    public function testDataSetCannotBeParsed(): void
    {
        $this->assertTrue(true);
    }
}
