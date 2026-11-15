<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelOutput;

use const STDERR;
use const STDOUT;
use function fwrite;
use PHPUnit\Framework\TestCase;

final class AWritesOutputTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        print "A: setUpBeforeClass()\n";
    }

    public static function tearDownAfterClass(): void
    {
        print "A: tearDownAfterClass()\n";
    }

    public function testWritesToStandardOutput(): void
    {
        fwrite(STDOUT, "A: written to STDOUT by the first test\n");

        $this->assertTrue(true);
    }

    public function testWritesToStandardError(): void
    {
        fwrite(STDERR, "A: written to STDERR by the second test\n");

        $this->assertTrue(true);
    }
}
