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
 * @testdox Basic ANSI color highlighting support
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
    public function testColorizePath(?string $prevPath, string $path, string $expected): void
    {
        $this->assertSame($expected, Color::colorizePath($path, $prevPath));
    }

    /**
     * @testdox dim($m) and colorize('dim',$m) return different ANSI codes
     */
    public function testDimAndColorizeDimAreDifferent(): void
    {
        $buffer = 'some string';
        $this->assertNotSame(Color::dim($buffer), Color::colorize('dim', $buffer));
    }

    /**
     * @testdox Visualize whitespace characters in $actual
     * @dataProvider whitespacedStringProvider
     */
    public function testVisibleWhitespace(string $actual, string $expected): void
    {
        $this->assertSame($expected, Color::visualizeWhitespace($actual));
    }

    /**
     * @dataProvider unnamedDataSetProvider
     */
    public function testPrettifyUnnamedDataprovider(int $value): void
    {
        $this->assertSame($value, $value);
    }

    /**
     * @dataProvider namedDataSetProvider
     */
    public function testPrettifyNamedDataprovider(int $value): void
    {
        $this->assertSame($value, $value);
    }

    /**
     * @testdox TestDox shows name of data set $_dataName with value $value
     * @dataProvider namedDataSetProvider
     */
    public function testTestdoxDatanameAsParameter(int $value): void
    {
        $this->assertSame($value, $value);
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
        $sepDim = Color::dim($sep);

        return [
            'null previous path' => [
                null,
                $sep . 'php' . $sep . 'unit' . $sep . 'test.phpt',
                $sepDim . 'php' . $sepDim . 'unit' . $sepDim . 'test.phpt',
            ],
            'empty previous path' => [
                '',
                $sep . 'php' . $sep . 'unit' . $sep . 'test.phpt',
                $sepDim . 'php' . $sepDim . 'unit' . $sepDim . 'test.phpt',
            ],
            'from root' => [
                $sep,
                $sep . 'php' . $sep . 'unit' . $sep . 'test.phpt',
                $sepDim . 'php' . $sepDim . 'unit' . $sepDim . 'test.phpt',
            ],
            'partial part' => [
                $sep . 'php' . $sep,
                $sep . 'php' . $sep . 'unit' . $sep . 'test.phpt',
                $sepDim . Color::dim('php') . $sepDim . 'unit' . $sepDim . 'test.phpt',
            ],
        ];
    }

    public function whitespacedStringProvider(): array
    {
        return [
            ['no-spaces', 'no-spaces'],
            [' space   invaders ', "\x1b[2m·\x1b[22mspace\e[2m···\e[22minvaders\e[2m·\e[22m"],
            ["\tindent, space and LF\n", "\e[2m⇥\e[22mindent,\e[2m·\e[22mspace\e[2m·\e[22mand\e[2m·\e[22mLF\e[2m↵\e[22m"],
        ];
    }

    public function unnamedDataSetProvider(): array
    {
        return [
            [1],
            [2],
        ];
    }

    public function namedDataSetProvider(): array
    {
        return [
            'one' => [1],
            'two' => [2],
        ];
    }
}
