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

use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\TestCase;
use function file_get_contents;
use function file_put_contents;

#[RunClassInSeparateProcess]
final class FilterSeparatedProcessTestSuiteTest extends TestCase
{
    public const string FILTER_SEPARATED_PROC_TS_COUNT_FILE_PATH = __DIR__ . '/temp/filter_separated_process_testsuite_count.txt';

    public function testFilterSeparatedProcessTestSuiteNoSkip(): void
    {
        $count = (int)(file_get_contents(self::FILTER_SEPARATED_PROC_TS_COUNT_FILE_PATH));
        file_put_contents(self::FILTER_SEPARATED_PROC_TS_COUNT_FILE_PATH, ++$count);

        $this->assertTrue(true);
    }

    public function testFilterSeparatedProcessTestSuiteSkip(): void
    {
        $count = (int)(file_get_contents(self::FILTER_SEPARATED_PROC_TS_COUNT_FILE_PATH));
        file_put_contents(self::FILTER_SEPARATED_PROC_TS_COUNT_FILE_PATH, ++$count);

        $this->assertTrue(true);
    }

    public function testFilterSeparatedProcessTestSuiteSkip2(): void
    {
        $count = (int)(file_get_contents(self::FILTER_SEPARATED_PROC_TS_COUNT_FILE_PATH));
        file_put_contents(self::FILTER_SEPARATED_PROC_TS_COUNT_FILE_PATH, ++$count);

        $this->assertTrue(true);
    }
}
