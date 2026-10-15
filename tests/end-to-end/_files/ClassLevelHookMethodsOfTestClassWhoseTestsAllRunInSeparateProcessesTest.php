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

use const FILE_APPEND;
use function file_put_contents;
use function getenv;
use function getmypid;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class ClassLevelHookMethodsOfTestClassWhoseTestsAllRunInSeparateProcessesTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        self::log('setUpBeforeClass');
    }

    public static function tearDownAfterClass(): void
    {
        self::log('tearDownAfterClass');
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }

    private static function log(string $method): void
    {
        $log = getenv('PHPUNIT_HOOK_METHOD_LOG');

        if ($log === false) {
            return;
        }

        $process = 'child';

        if (getenv('PHPUNIT_TEST_RUNNER_PROCESS_ID') === (string) getmypid()) {
            $process = 'parent';
        }

        file_put_contents($log, $method . '() called in ' . $process . " process\n", FILE_APPEND);
    }
}
