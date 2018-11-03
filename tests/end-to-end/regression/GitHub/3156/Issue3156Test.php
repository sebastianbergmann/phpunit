<?php
declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Test;

use PHPUnit\Framework\TestCase;
use stdClass;

class Issue3156Test extends TestCase
{
    public function testConstants(): stdClass
    {
        $this->assertStringEndsWith('/', '/');

        return new stdClass;
    }

    public function dataSelectOperatorsProvider(): array
    {
        return [
            ['1'],
            ['2'],
        ];
    }

    /**
     * @depends testConstants
     * @dataProvider dataSelectOperatorsProvider
     */
    public function testDependsRequire(string $val, stdClass $obj): void
    {
        $this->assertStringEndsWith('/', '/');
    }
}
