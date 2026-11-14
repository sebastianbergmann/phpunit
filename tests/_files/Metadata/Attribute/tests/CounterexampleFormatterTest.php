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

use PHPUnit\Framework\Attributes\CounterexampleFormatter;
use PHPUnit\Framework\Attributes\CounterexampleFormatterExternal;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class CounterexampleFormatterTest extends TestCase
{
    #[Property]
    #[CounterexampleFormatter('methodName')]
    public function testOne(int $value): void
    {
    }

    #[Property]
    #[CounterexampleFormatterExternal('ClassName', 'methodName')]
    public function testTwo(int $value): void
    {
    }
}
