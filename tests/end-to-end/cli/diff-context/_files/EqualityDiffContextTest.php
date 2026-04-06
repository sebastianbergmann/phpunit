<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\DiffContext;

use PHPUnit\Framework\TestCase;

final class EqualityDiffContextTest extends TestCase
{
    public function testArrayEquality(): void
    {
        $expected = [
            'key01' => 'val01',
            'key02' => 'val02',
            'key03' => 'val03',
            'key04' => 'val04',
            'key05' => 'val05',
            'key06' => 'val06',
            'key07' => 'val07',
            'key08' => 'val08',
            'key09' => 'val09',
            'key10' => 'val10',
            'key11' => 'val11',
            'key12' => 'val12',
            'key13' => 'val13',
            'key14' => 'val14',
            'key15' => 'val15',
        ];

        $actual          = $expected;
        $actual['key08'] = 'CHANGED';

        $this->assertEquals($expected, $actual);
    }
}
