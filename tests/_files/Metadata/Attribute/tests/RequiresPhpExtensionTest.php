<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Attribute;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

#[RequiresPhpExtension('foo')]
final class RequiresPhpExtensionTest extends TestCase
{
    #[RequiresPhpExtension('bar', '>= 1.0')]
    public function testOne(): void
    {
    }

    #[RequiresPhpExtension('baz', '^1.0')]
    public function testTwo(): void
    {
    }
}
