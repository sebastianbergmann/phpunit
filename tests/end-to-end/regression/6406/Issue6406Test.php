<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6406;

use const INF;
use const NAN;
use PHPUnit\Framework\TestCase;

final class Issue6406Test extends TestCase
{
    public static function provider(): array
    {
        return [
            'inf' => [INF],
            'nan' => [NAN],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testOne($value): void
    {
        $this->assertTrue(true);
    }
}
