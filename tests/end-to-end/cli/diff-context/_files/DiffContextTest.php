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

final class DiffContextTest extends TestCase
{
    public function testMultiLineDiff(): void
    {
        $format = "line01\nline02\nline03\nline04\nline05\nline06\nline07\nline08\nline09\nline10\nline11\nline12\nline13\nline14\nline15\nline16\nline17\nline18\nline19\nline20";
        $actual = "line01\nline02\nline03\nline04\nline05\nline06\nline07\nline08\nline09\nLINE10\nline11\nline12\nline13\nline14\nline15\nline16\nline17\nline18\nline19\nline20";

        $this->assertStringMatchesFormat($format, $actual);
    }
}
