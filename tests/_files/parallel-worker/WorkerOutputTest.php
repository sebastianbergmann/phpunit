<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorker;

use const STDERR;
use function fwrite;
use PHPUnit\Framework\TestCase;

/**
 * A test class that writes output that is not buffered as the output of a
 * test: before its first test, while its tests run, and after its last test.
 */
final class WorkerOutputTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        print "before the first test\n";
    }

    public static function tearDownAfterClass(): void
    {
        print "after the last test\n";

        fwrite(STDERR, "written to the standard error after the last test\n");
    }

    public function testOne(): void
    {
        fwrite(STDERR, "first test\n");

        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        fwrite(STDERR, "second test\n");

        $this->assertTrue(true);
    }
}
