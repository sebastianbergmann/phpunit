<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Attributes\TestDoxFormatter;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class TestDoxFormatterErrorTest extends TestCase
{
    public static function formatterThatThrows(): string
    {
        throw new RuntimeException('message');
    }

    public function formatterThatIsNotStatic(): string
    {
        return 'not static';
    }

    #[TestDoxFormatter('formatterThatDoesNotExist')]
    public function testWithFormatterThatDoesNotExist(): void
    {
    }

    #[TestDoxFormatter('formatterThatIsNotPublic')]
    public function testWithFormatterThatIsNotPublic(): void
    {
    }

    #[TestDoxFormatter('formatterThatIsNotStatic')]
    public function testWithFormatterThatIsNotStatic(): void
    {
    }

    #[TestDoxFormatter('formatterThatThrows')]
    public function testWithFormatterThatThrows(): void
    {
    }

    private static function formatterThatIsNotPublic(): string
    {
        return 'not public';
    }
}
