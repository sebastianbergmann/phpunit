<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace IssueTest6105;

use function header;
use function ob_get_clean;
use function ob_start;
use function xdebug_get_headers;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class IssueTest6105 extends TestCase
{
    public function test_1(): void
    {
        $this->assertTrue(true);
    }

    #[RunInSeparateProcess]
    #[RequiresPhpExtension('xdebug')]
    public function test_case_2_check(): void
    {
        ob_start();
        header('X-Test: Testing');
        print 'asd';
        $content = ob_get_clean();

        $this->assertSame('asd', $content);
        $this->assertSame(['X-Test: Testing'], xdebug_get_headers());
    }
}
