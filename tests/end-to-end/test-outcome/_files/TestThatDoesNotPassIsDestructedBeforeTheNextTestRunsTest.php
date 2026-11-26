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

use Exception;
use PHPUnit\Framework\TestCase;

final class TestThatDoesNotPassIsDestructedBeforeTheNextTestRunsTest extends TestCase
{
    private static int $destructsDone = 0;

    public function __destruct()
    {
        self::$destructsDone++;
    }

    public function testErrors(): void
    {
        throw new Exception('error');
    }

    public function testFails(): void
    {
        $this->assertSame(1, self::$destructsDone);

        $this->fail('failure');
    }

    public function testPasses(): void
    {
        $this->assertSame(2, self::$destructsDone);
    }
}
