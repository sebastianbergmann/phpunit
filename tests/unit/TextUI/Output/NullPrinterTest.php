<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(NullPrinter::class)]
#[Small]
#[Group('textui')]
final class NullPrinterTest extends TestCase
{
    public function testDoesNotPrint(): void
    {
        $this->expectOutputString('');

        (new NullPrinter)->print('message');
    }

    public function testDoesNotFlush(): void
    {
        $this->expectOutputString('');

        (new NullPrinter)->flush();
    }
}
