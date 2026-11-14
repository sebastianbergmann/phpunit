<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Properties;

use PHPUnit\Framework\Attributes\CounterexampleFormatter;
use PHPUnit\Framework\Attributes\CounterexampleFormatterExternal;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class CounterexampleFormatterPropertiesTest extends TestCase
{
    public static function format(int $value): string
    {
        return (string) $value;
    }

    public function notStatic(int $value): string
    {
        return (string) $value;
    }

    #[Property]
    #[CounterexampleFormatter('format')]
    public function testWithFormatter(int $value): void
    {
    }

    #[Property]
    #[CounterexampleFormatterExternal(self::class, 'format')]
    public function testWithFormatterOfAnotherClass(int $value): void
    {
    }

    #[Property]
    public function testWithoutFormatter(int $value): void
    {
    }

    #[Property]
    #[CounterexampleFormatter('doesNotExist')]
    public function testWithFormatterThatDoesNotExist(int $value): void
    {
    }

    #[Property]
    #[CounterexampleFormatter('notPublic')]
    public function testWithFormatterThatIsNotPublic(int $value): void
    {
    }

    #[Property]
    #[CounterexampleFormatter('notStatic')]
    public function testWithFormatterThatIsNotStatic(int $value): void
    {
    }

    #[Property]
    #[CounterexampleFormatter('format')]
    #[CounterexampleFormatterExternal(self::class, 'format')]
    public function testWithMoreThanOneFormatter(int $value): void
    {
    }

    private static function notPublic(int $value): string
    {
        return (string) $value;
    }
}
