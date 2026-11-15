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

use const STDOUT;
use function fclose;
use PHPUnit\Framework\TestCase;

final class ClosesStandardOutputTest extends TestCase
{
    public function testClosesStandardOutput(): void
    {
        fclose(STDOUT);

        $this->assertTrue(true);
    }

    public function testRunsAfterStandardOutputWasClosed(): void
    {
        $this->assertTrue(true);
    }
}
