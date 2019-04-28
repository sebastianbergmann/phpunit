<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

final class TestWithDifferentOutput extends TestCase
{
    public function testThatDoesNotGenerateOutput(): void
    {
        $this->assertTrue(true);
    }

    public function testThatExpectsOutputRegex(): void
    {
        $this->expectOutputRegex('.*');

        print 'Hello!';
    }

    public function testThatExpectsOutputString(): void
    {
        $this->expectOutputString('Hello!');

        print 'Hello!';
    }

    public function testThatGeneratesOutput(): void
    {
        print 'Hello!';

        $this->assertTrue(true);
    }
}
