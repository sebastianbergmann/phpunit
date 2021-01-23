<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Code\Test
 */
final class TestTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $className = self::class;
        $testName  = 'foo';

        $test = new Test(
            $className,
            $testName
        );

        $this->assertSame($className, $test->className());
        $this->assertSame($testName, $test->testName());
    }
}
