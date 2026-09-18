<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelSerialization;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class PlainObjectDataTest extends TestCase
{
    public static function objectProvider(): array
    {
        $object = new stdClass;

        $object->value = 'plain';

        return [
            [$object],
        ];
    }

    #[DataProvider('objectProvider')]
    public function testReceivesAPlainObject(object $value): void
    {
        $this->assertSame('plain', $value->value);
    }
}
