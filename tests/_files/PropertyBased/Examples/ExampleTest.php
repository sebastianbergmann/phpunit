<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Examples;

use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    /**
     * @var list<mixed>
     */
    public static array $arguments = [];

    public function testRecordsItsArguments(int $number, string $string): void
    {
        self::$arguments = [$number, $string];

        $this->assertTrue(true);
    }
}
