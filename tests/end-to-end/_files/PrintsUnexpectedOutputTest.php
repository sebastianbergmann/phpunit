<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\TestCase;

final class PrintsUnexpectedOutputTest extends TestCase
{
    public function testPassingTestThatPrintsOutput(): void
    {
        print "output of passing test\n";

        $this->assertTrue(true);
    }

    public function testFailingTestThatPrintsOutput(): void
    {
        print "output of failing test\n";

        $this->assertTrue(false);
    }

    public function testPrintsOnlyLineFeed(): void
    {
        print "\n";

        $this->assertTrue(true);
    }
}
