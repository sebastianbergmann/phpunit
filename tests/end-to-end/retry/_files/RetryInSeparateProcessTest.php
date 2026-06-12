<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Retry;

use function file_get_contents;
use function file_put_contents;
use function is_file;
use function sys_get_temp_dir;
use PHPUnit\Framework\Attributes\Retry;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class RetryInSeparateProcessTest extends TestCase
{
    #[Retry(2)]
    #[RunInSeparateProcess]
    public function testOne(): void
    {
        $file  = sys_get_temp_dir() . '/phpunit-retry-in-separate-process-test.counter';
        $count = 0;

        if (is_file($file)) {
            $count = (int) file_get_contents($file);
        }

        $count++;

        file_put_contents($file, (string) $count);

        $this->assertGreaterThan(1, $count);
    }
}
