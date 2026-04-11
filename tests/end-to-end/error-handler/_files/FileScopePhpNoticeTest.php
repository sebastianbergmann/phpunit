<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ErrorHandler;

use PHPUnit\Framework\TestCase;

$f = static function (): void
{
};

$a = &$f();

final class FileScopePhpNoticeTest extends TestCase
{
    public function testSuccess(): void
    {
        $this->assertTrue(true);
    }
}
