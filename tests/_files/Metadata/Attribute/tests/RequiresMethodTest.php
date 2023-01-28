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

use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\TestCase;

#[RequiresMethod('ClassName', 'methodName')]
final class RequiresMethodTest extends TestCase
{
    #[RequiresMethod('AnotherClassName', 'anotherMethodName')]
    public function testOne(): void
    {
    }
}
