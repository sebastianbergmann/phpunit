<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class ExhaustedExamplesTest extends TestCase
{
    #[Property]
    public function testHasTwoExamples(bool $flag): void
    {
        $this->assertIsBool($flag);
    }

    /**
     * @param int<0, 2> $value
     */
    #[Property]
    public function testHasSixExamples(int $value, bool $flag): void
    {
        $this->assertLessThanOrEqual(2, $value);
    }

    #[Property]
    public function testHasOneExample(): void
    {
        $this->assertTrue(true);
    }

    #[Property]
    public function testRejectsEveryExample(bool $flag): void
    {
        $this->assume(false);
    }
}
