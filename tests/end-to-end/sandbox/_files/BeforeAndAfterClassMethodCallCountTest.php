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
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

//#[RunTestsInSeparateProcesses]
#[RunClassInSeparateProcess]
final class BeforeAndAfterClassMethodCallCountTest extends TestCase
{
    public const string BEFORE_CALL_COUNT_FILE_PATH  = __DIR__ . '/before_method_call_count.txt';
    public const string AFTER_CALL_COUNT_FILE_PATH  = __DIR__ . '/after_method_call_count.txt';

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

    public function testBeforeAndAfterClassMethodCallCount(): void
    {
        $this->assertEquals(file_get_contents(self::BEFORE_CALL_COUNT_FILE_PATH), '1', 'before_method_call_count');
        $this->assertEquals(file_get_contents(self::AFTER_CALL_COUNT_FILE_PATH), '0', 'after_method_call_count');
    }
}
