<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\TestDox;

use PHPUnit\Framework\TestCase;

/**
 * @testdox Basic ANSI color support
 */
class ColorTest extends TestCase
{
    /**
     * @testdox Colorize with $_dataName
     * @dataProvider colorizeProvider
     */
    public function testColorize(string $color, string $buffer, string $expected): void
    {
        $this->assertSame($expected, Color::colorize($color, $buffer));
    }

    /**
     * @testdox Colorize path $path after $prevPath
     * @dataProvider colorizePathProvider
     */
    public function testColorizePath(string $prevPath, string $path, string $expected): void
    {
        $this->assertSame($expected, Color::colorizePath($path, $prevPath));
    }

    public function colorizeProvider(): array
    {
        return [
            'no color'        => ['', 'string', 'string'],
            'one color'       => ['fg-blue', 'string', "\x1b[34mstring\x1b[0m"],
            'multiple colors' => ['bold,dim,fg-blue,bg-yellow', 'string', "\x1b[1;2;34;43mstring\x1b[0m"],
        ];
    }

    public function colorizePathProvider(): array
    {
        $sep    = \DIRECTORY_SEPARATOR;
        $sepDim = Color::colorize('dim', $sep);

        return [
            'no previous path' => [
                $sep,
                $sep . 'php' . $sep . 'unit' . $sep . 'test.phpt',
                $sepDim . 'php' . $sepDim . 'unit' . $sepDim . 'test.phpt',
            ],
            'partial part' => [
                $sep . 'php' . $sep,
                $sep . 'php' . $sep . 'unit' . $sep . 'test.phpt',
                $sepDim . Color::colorize('dim', 'php') . $sepDim . 'unit' . $sepDim . 'test.phpt',
            ],
        ];
    }
}
