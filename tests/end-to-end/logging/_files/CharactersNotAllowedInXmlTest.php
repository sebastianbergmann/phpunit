<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Logging;

use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CharactersNotAllowedInXmlTest extends TestCase
{
    public static function provider(): array
    {
        return [
            "\x07data set" => [true],
        ];
    }

    public function testOne(): void
    {
        $this->fail("\e[31mmessage\e[0m");
    }

    public function testTwo(): void
    {
        throw new Exception("message that is not valid UTF-8: \xe4");
    }

    #[DataProvider('provider')]
    public function testThree(bool $value): void
    {
        $this->assertTrue($value);
    }

    public function testFour(): void
    {
        $this->assertTrue(true);
    }
}
