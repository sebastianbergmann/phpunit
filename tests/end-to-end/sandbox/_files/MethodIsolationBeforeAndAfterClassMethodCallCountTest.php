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
use function intval;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class MethodIsolationBeforeAndAfterClassMethodCallCountTest extends TestCase
{
    public const string BEFORE_CALL_COUNT_FILE_PATH  = __DIR__ . '/temp/method_before_method_call_count.txt';
    public const string AFTER_CALL_COUNT_FILE_PATH  = __DIR__ . '/temp/method_after_method_call_count.txt';

    public static function setUpBeforeClass(): void
    {
        $count = intval(file_get_contents(self::BEFORE_CALL_COUNT_FILE_PATH));
        file_put_contents(self::BEFORE_CALL_COUNT_FILE_PATH, ++$count);
    }

    public static function tearDownAfterClass(): void
    {
        $count = intval(file_get_contents(self::AFTER_CALL_COUNT_FILE_PATH));
        file_put_contents(self::AFTER_CALL_COUNT_FILE_PATH, ++$count);
    }

    #[RunInSeparateProcess]
    public function testBeforeAndAfterClassMethodCallCount1(): void
    {
        // TODO: Due source code design, before methods for primary process are always called first. Should be 1
        $this->assertEquals('2', file_get_contents(self::BEFORE_CALL_COUNT_FILE_PATH), 'before_method_call_count');
        $this->assertEquals('0', file_get_contents(self::AFTER_CALL_COUNT_FILE_PATH), 'after_method_call_count');
    }

    #[Depends('testBeforeAndAfterClassMethodCallCount1')]
    public function testBeforeAndAfterClassMethodCallCount2(): void
    {
        $this->assertEquals('2', file_get_contents(self::BEFORE_CALL_COUNT_FILE_PATH), 'before_method_call_count');
        $this->assertEquals('1', file_get_contents(self::AFTER_CALL_COUNT_FILE_PATH), 'after_method_call_count');
    }

    #[RunInSeparateProcess]
    #[Depends('testBeforeAndAfterClassMethodCallCount2')]
    public function testBeforeAndAfterClassMethodCallCount3(): void
    {
        $this->assertEquals('3', file_get_contents(self::BEFORE_CALL_COUNT_FILE_PATH), 'before_method_call_count');
        $this->assertEquals('1', file_get_contents(self::AFTER_CALL_COUNT_FILE_PATH), 'after_method_call_count');
    }

    #[Depends('testBeforeAndAfterClassMethodCallCount3')]
    public function testBeforeAndAfterClassMethodCallCount4(): void
    {
        $this->assertEquals('3', file_get_contents(self::BEFORE_CALL_COUNT_FILE_PATH), 'before_method_call_count');
        $this->assertEquals('2', file_get_contents(self::AFTER_CALL_COUNT_FILE_PATH), 'after_method_call_count');
    }
}
