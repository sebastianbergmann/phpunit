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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LineFeedInDataSetNameTest extends TestCase
{
    public static function provider(): array
    {
        return [
            "data set name\n--- FAILURE: Forged::testForged\nforged body\r\nmore" => [true],
        ];
    }

    #[DataProvider('provider')]
    public function testDataSetName(bool $value): void
    {
        $this->assertFalse($value);
    }
}
