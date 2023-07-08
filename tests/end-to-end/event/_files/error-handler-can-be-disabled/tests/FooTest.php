<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event\ErrorHandlerCanBeDisabled;

use function sys_get_temp_dir;
use function tempnam;
use Exception;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;

final class FooTest extends TestCase
{
    #[WithoutErrorHandler]
    public function testMethodA(): void
    {
        $fileName = tempnam(sys_get_temp_dir(), 'RLT') . '/missing/directory';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to open stream');

        (new Foo)->methodA($fileName);
    }

    #[WithoutErrorHandler]
    public function testMethodB(): void
    {
        $this->assertSame('Triggering', (new Foo)->methodB()['message']);
    }
}
