<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\OpenTestReporting;

use PHPUnit\Framework\TestCase;

final class PhpNoticeIssueTest extends TestCase
{
    public function testOne(): void
    {
        $f = static function (): void
        {};
        $a = &$f();

        $this->assertTrue(true);
    }
}
