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
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use stdClass;

class Issue3156Test extends TestCase
{
    public static function dataSelectOperatorsProvider(): array
    {
        return [
            ['1'],
            ['2'],
        ];
    }

    public function testConstants(): stdClass
    {
        $this->assertStringEndsWith('/', '/');

        return new stdClass;
    }

    #[Depends('testConstants')]
    #[DataProvider('dataSelectOperatorsProvider')]
    public function testDependsRequire(string $val, stdClass $obj): void
    {
        $this->assertStringEndsWith('/', '/');
    }
}
