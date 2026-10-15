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

use function getenv;
use function getmypid;
use Exception;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class ExceptionInAfterClassMethodInChildProcessTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        if (getenv('PHPUNIT_TEST_RUNNER_PROCESS_ID') !== (string) getmypid()) {
            throw new Exception('message');
        }
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
