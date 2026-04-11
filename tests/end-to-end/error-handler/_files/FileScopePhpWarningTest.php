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

use function file_get_contents;
use PHPUnit\Framework\TestCase;

file_get_contents('/nonexistent/file/for/phpunit/test');

final class FileScopePhpWarningTest extends TestCase
{
    public function testSuccess(): void
    {
        $this->assertTrue(true);
    }
}
