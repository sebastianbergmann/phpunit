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

use function file_exists;
use function file_get_contents;
use function getmypid;
use function unlink;
use PHPUnit\Framework\TestCase;

final class SeparateClassRunMethodInNewProcessTest extends TestCase
{
    public const PROCESS_ID_FILE_PATH      = __DIR__ . '/parent_process_id.txt';
    public const INITIAL_PARENT_PROCESS_ID = 0;
    public const INITIAL_PROCESS_ID        = 1;
    public static $parentProcessId         = self::INITIAL_PARENT_PROCESS_ID;
    public static $processId               = self::INITIAL_PROCESS_ID;

    public static function setUpBeforeClass(): void
    {
        if (file_exists(self::PROCESS_ID_FILE_PATH)) {
            self::$parentProcessId = (int) file_get_contents(self::PROCESS_ID_FILE_PATH);
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (file_exists(self::PROCESS_ID_FILE_PATH)) {
            unlink(self::PROCESS_ID_FILE_PATH);
        }
    }

    public function testTestMethodIsRunInSeparateProcess(): void
    {
        self::$processId = getmypid();

        $this->assertNotSame(self::INITIAL_PROCESS_ID, self::$processId);
        $this->assertNotSame(self::INITIAL_PARENT_PROCESS_ID, self::$parentProcessId);
        $this->assertNotSame(self::$processId, self::$parentProcessId);
    }
}
