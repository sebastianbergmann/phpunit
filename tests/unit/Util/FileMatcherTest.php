<?php

namespace PHPUnit\Util;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FileMatcherTest extends TestCase
{
    public function testExceptionIfPathIsNotAbsolute(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Path "foo/bar" must be absolute');
        FileMatcher::match('foo/bar', new FileMatcherPattern(''));
    }

    /**
     * @param array<FileMatcherPattern,bool> $matchMap
     */
    #[DataProvider('provideMatch')]
    public function testMatch(FileMatcherPattern $pattern, array $matchMap): void
    {
        self::assertMap($pattern, $matchMap);
    }

    /**
     * @param array<FileMatcherPattern,bool> $matchMap
     */
    #[DataProvider('provideWildcard')]
    public function testWildcard(FileMatcherPattern $pattern, array $matchMap): void
    {
        self::assertMap($pattern, $matchMap);
    }

    /**
     * @return Generator<string,array{FileMatcherPattern,array<string,bool>}>
     */
    public static function provideMatch(): Generator
    {
        yield 'exact path' => [
            new FileMatcherPattern('/path/to/example/Foo.php'),
            [
                '/path/to/example/Foo.php' => true,
                '/path/to/example/Bar.php' => false,
            ],
        ];

        yield 'directory' => [
            new FileMatcherPattern('/path/to'),
            [
                '/path/to' => true,
                '/path/to/example/Foo.php' => true,
                '/path/foo/Bar.php' => false,
            ],
        ];
    }

    /**
     * @return Generator<string,array{FileMatcherPattern,array<string,bool>}>
     */
    public static function provideWildcard(): Generator
    {

        yield 'leaf wildcard' => [
            new FileMatcherPattern('/path/*'),
            [
                '/path/foo/bar' => true,
                '/path/foo/baz' => true,
                '/path/baz.php' => true,
                '/path/foo/baz/boo.php' => true,
                '/path/example/file.php' => false,
                '/' => false,
                '' => false,
            ],
        ];

        yield 'leaf directory wildcard' => [
            new FileMatcherPattern('/path/*'),
            [
                '/path/foo/bar' => true,
                '/path/foo/baz' => true,
                '/path/foo/baz/boo.php' => true,
                '/path/example/file.php' => false,
                '/' => false,
                '' => false,
            ],
       ];
        yield 'segment directory wildcard' => [
            new FileMatcherPattern('/path/*/bar'),
            [
                '/path/foo/bar' => true,
                '/path/foo/baz' => false,
                '/path/foo/bar/boo.php' => true,
                '/foo/bar/file.php' => false,
            ],
       ];

        yield 'multiple segment directory wildcards' => [
            new FileMatcherPattern('/path/*/example/*/bar'),
            [
                '/path/zz/example/aa/bar' => true,
                '/path/zz/example/aa/bar/foo' => true,
                '/path/example/aa/bar/foo' => false,
                '/path/zz/example/bb/foo' => false,
            ],
        ];

        yield 'partial wildcard' => [
            new FileMatcherPattern('/path/f*'),
            [
                '/path/foo/bar' => true,
                '/path/foo/baz' => true,
                '/path/boo' => false,
                '/path/boo/example/file.php' => false,
            ],
       ];

        yield 'partial segment wildcard' => [
            new FileMatcherPattern('/path/f*/bar'),
            [
                '/path/foo/bar' => true,
                '/path/faa/bar' => true,
                '/path/foo/baz' => false,
                '/path/boo' => false,
                '/path/boo/example/file.php' => false,
            ],
       ];
    }

    /**
     * @param array<FileMatcherPattern,bool> $matchMap
     */
    private static function assertMap(FileMatcherPattern $pattern, array $matchMap): void
    {
        foreach ($matchMap as $candidate => $shouldMatch) {
            self::assertSame($shouldMatch, FileMatcher::match($candidate, $pattern));
        }
    }
}
