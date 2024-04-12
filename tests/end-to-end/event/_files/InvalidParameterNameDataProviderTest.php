<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InvalidParameterNameDataProviderTest extends TestCase
{
    public static function values(): array
    {
        return [
            ['value1' => true, 'value2' => true],
            ['value3' => true, 'value4' => true],
        ];
    }

    #[DataProvider('values')]
    public function testSuccess(bool $value1, bool $value2): void
    {
        $this->assertTrue($value1);
        $this->assertTrue($value2);
    }
}
