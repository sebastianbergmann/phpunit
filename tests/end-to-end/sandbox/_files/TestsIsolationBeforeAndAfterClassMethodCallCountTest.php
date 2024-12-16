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

use function file_get_contents;
use function file_put_contents;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class TestsIsolationBeforeAndAfterClassMethodCallCountTest extends TestCase
{
    public const string BEFORE_CALL_COUNT_FILE_PATH = __DIR__ . '/temp/tests_before_method_call_count.txt';
    public const string AFTER_CALL_COUNT_FILE_PATH  = __DIR__ . '/temp/tests_after_method_call_count.txt';

    public static function setUpBeforeClass(): void
    {
        $count = (int) (file_get_contents(self::BEFORE_CALL_COUNT_FILE_PATH));
        file_put_contents(self::BEFORE_CALL_COUNT_FILE_PATH, ++$count);
    }

    public static function tearDownAfterClass(): void
    {
        $count = (int) (file_get_contents(self::AFTER_CALL_COUNT_FILE_PATH));
        file_put_contents(self::AFTER_CALL_COUNT_FILE_PATH, ++$count);
    }

    public function testBeforeAndAfterClassMethodCallCount1(): void
    {
        $this->assertEquals('1', file_get_contents(self::BEFORE_CALL_COUNT_FILE_PATH), 'before_method_call_count');
        $this->assertEquals('0', file_get_contents(self::AFTER_CALL_COUNT_FILE_PATH), 'after_method_call_count');
    }

    public function testBeforeAndAfterClassMethodCallCount2(): void
    {
        $this->assertEquals('2', file_get_contents(self::BEFORE_CALL_COUNT_FILE_PATH), 'before_method_call_count');
        $this->assertEquals('1', file_get_contents(self::AFTER_CALL_COUNT_FILE_PATH), 'after_method_call_count');
    }

    public function testBeforeAndAfterClassMethodCallCount3(): void
    {
        $this->assertEquals('3', file_get_contents(self::BEFORE_CALL_COUNT_FILE_PATH), 'before_method_call_count');
        $this->assertEquals('2', file_get_contents(self::AFTER_CALL_COUNT_FILE_PATH), 'after_method_call_count');
    }
}
