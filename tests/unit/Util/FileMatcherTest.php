<?php

namespace PHPUnit\Util;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(FileMatcher::class)]
#[Small]
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
    #[DataProvider('provideWildcard')]
    #[DataProvider('provideGlobstar')]
    #[DataProvider('provideQuestionMark')]
    #[DataProvider('provideCharacterGroup')]
    #[DataProvider('provideRelativePathSegments')]
    public function testMatch(FileMatcherPattern $pattern, array $matchMap): void
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
                '/path/example/file.php' => true,
                '/' => false,
            ],
        ];

        yield 'leaf directory wildcard' => [
            new FileMatcherPattern('/path/*'),
            [
                '/path/foo/bar' => true,
                '/path/foo/baz' => true,
                '/path/foo/baz/boo.php' => true,
                '/path/example/file.php' => true,
                '/' => false,
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
     * @return Generator<string,array{FileMatcherPattern,array<string,bool>}>
     */
    public static function provideGlobstar(): Generator
    {
        yield 'leaf globstar at root' => [
            new FileMatcherPattern('/**'),
            [
                '/foo' => true,
                '/foo/bar' => true,
                '/' => true, // matches zero or more
            ],
        ];

        yield 'leaf globstar' => [
            new FileMatcherPattern('/foo/**'),
            [
                '/foo' => true,
                '/foo/foo' => true,
                '/foo/foo/baz.php' => true,
                '/bar/foo' => false,
                '/bar/foo/baz' => false,
            ],
        ];

        // partial match does not work with globstar
        yield 'partial leaf globstar' => [
            new FileMatcherPattern('/foo/emm**'),
            [
                '/foo/emmer' => false,
                '/foo/emm' => false,
                '/foo/emm/bar' => false,
                '/' => false,
            ],
        ];

        yield 'segment globstar' => [
            new FileMatcherPattern('/foo/emm/**/bar'),
            [
                '/foo/emm/bar' => true,
                '/foo/emm/foo/bar' => true,
                '/baz/emm/foo/bar/boo' => false,
                '/baz/emm/foo/bar' => false,
                '/foo/emm/barfoo' => false,
                '/foo/emm/' => false,
                '/foo/emm' => false,
            ],
        ];

        // TODO: this edge case
        return;
        // PHPUnit will match ALL directories within `/foo` with `/foo/A**`
        // however it will NOT match anything with `/foo/Aa**`
        //
        // This is likely a bug and so we could consider "fixing" it
        yield 'EDGE: segment globstar with wildcard' => [
            new FileMatcherPattern('/foo/emm/**/*ar'),
            [
                '/foo/emm/bar' => true,
                '/foo/emm/far' => true,
                '/foo/emm/foo/far' => true,
                '/foo/emm/foo/far' => true,
                '/foo/emm/foo/bar/far' => true,
                '/baz/emm/foo/bar/boo' => true,
                '/baz/emm/foo/bad' => false,
                '/baz/emm/foo/bad/boo' => false,
            ],
        ];
    }

    /**
     * @return Generator<string,array{FileMatcherPattern,array<string,bool>}>
     */
    public static function provideQuestionMark(): Generator
    {
        yield 'question mark at root' => [
            new FileMatcherPattern('/?'),
            [
                '/' => false,
                '/f' => true,
                '/foo' => false,
                '/f/emm/foo/bar' => true,
                '/foo/emm/foo/bar' => false,
            ],
        ];
        yield 'question mark at leaf' => [
            new FileMatcherPattern('/foo/?'),
            [
                '/foo' => false,
                '/foo/' => false,
                '/foo/a' => true,
                '/foo/ab' => false,
                '/foo/a/c' => true,
            ],
        ];
        yield 'question mark at segment start' => [
            new FileMatcherPattern('/foo/?ar'),
            [
                '/' => false,
                '/foo' => false,
                '/foo/' => false,
                '/foo/aa' => false,
                '/foo/aar' => true,
                '/foo/aarg' => false,
                '/foo/aar/barg' => true,
                '/foo/bar' => true,
                '/foo/ab/c' => false,
            ],
        ];
        yield 'question mark in segment' => [
            new FileMatcherPattern('/foo/f?o'),
            [
                '/foo' => false,
                '/foo/' => false,
                '/foo/foo' => true,
                '/foo/boo' => false,
                '/foo/foo/true' => true,
            ],
        ];
        yield 'consecutive question marks' => [
            new FileMatcherPattern('/foo/???'),
            [
                '/foo' => false,
                '/foo/' => false,
                '/foo/bar' => true,
                '/foo/car' => true,
                '/foo/the/test/will/pass' => true,
                '/bar/the/test/will/not/pass' => false,
            ],
        ];
        yield 'multiple question marks in segment' => [
            new FileMatcherPattern('/foo/?a?'),
            [
                '/foo/car' => true,
                '/foo/ccr' => false,
            ],
        ];
        yield 'multiple question marks in segments' => [
            new FileMatcherPattern('/foo/?a?/bar/f?a'),
            [
                '/foo' => false,
                '/foo/aaa' => false,
                '/foo/aaa/bar' => false,
                '/foo/aaa/bar/' => false,
                '/foo/bar/zaa' => false,
                '/foo/car/bar/faa' => true,
            ],
        ];
        yield 'tailing question mark' => [
            new FileMatcherPattern('/foo/?a?/bar/fa?'),
            [
                '/foo/car' => false,
                '/foo/car/bar/faa' => true,
                '/foo/ccr' => false,
                '/foo/bar/zaa' => false,
            ],
        ];
    }

    /**
     * @return Generator<string,array{FileMatcherPattern,array<string,bool>}>
     */
    public static function provideCharacterGroup(): Generator
    {
        yield 'unterminated char group' => [
            new FileMatcherPattern('/[AB'),
            [
                '/[' => false,
                '/[A' => false,
                '/[AB' => true,
                '/[AB/foo' => true,
            ],
        ];
        yield 'single char leaf' => [
            new FileMatcherPattern('/[A]'),
            [
                '/A' => true,
                '/B' => false,
            ],
        ];
        yield 'single char segment' => [
            new FileMatcherPattern('/a/[B]/c'),
            [
                '/a' => false,
                '/a/B' => true,
                '/a/B/c' => true,
                '/a/Z/c' => false,
            ],
        ];
        yield 'multichar' => [
            new FileMatcherPattern('/a/[ABC]/c'),
            [
                '/a' => false,
                '/a/A' => true,
                '/a/B/c' => true,
                '/a/C/c' => true,
                '/a/Z/c' => false,
                '/a/Za/c' => false,
                '/a/Aaa/c' => false,
            ],
        ];

        yield 'matching is case sensitive' => [
            new FileMatcherPattern('/a/[ABC]/c'),
            [
                '/a/a' => false,
                '/a/b/c' => false,
                '/a/c/c' => false,
            ],
        ];

        // https://man7.org/linux/man-pages/man7/glob.7.html
        // example from glob manpage
        yield 'square bracket in char group' => [
            new FileMatcherPattern('/[][!]'),
            [
                '/[hello' => true,
                '/[' => true,
                '/!' => true,
                '/!bang' => true,
                '/a' => false,
                '/' => false,
            ],
        ];

        yield 'match ranges' => [
            new FileMatcherPattern('/a/[a-c]/c'),
            [
                '/a/a' => false,
                '/a/z/c' => false,
                '/a/b/c' => true,
                '/a/c/c' => true,
                '/a/d/c' => false,
                '/a/c/d' => false,
            ],
        ];

        yield 'multiple match ranges' => [
            new FileMatcherPattern('/a/[a-c0-8]/c'),
            [
                '/a/a' => false,
                '/a/0/c' => true,
                '/a/2/c' => true,
                '/a/8/c' => true,
                '/a/9/c' => false,
                '/a/c/c' => true,
                '/a/a/c' => true,
                '/a/d/c' => false,
            ],
        ];

        yield 'dash in group' => [
            new FileMatcherPattern('/a/[-]/c'),
            [
                '/a/-' => true,
                '/a/-/fo' => true,
                '/a/a/fo' => false,
            ],
        ];

        yield 'range prefix dash' => [
            new FileMatcherPattern('/a/[-a-c]/c'),
            [
                '/a/a' => false,
                '/a/-' => true,
                '/a/d' => false,
                '/a/-b/c' => false,
                '/a/a/fo' => true,
                '/a/c/fo' => true,
                '/a/d/fo' => false,
            ],
        ];

        yield 'range infix dash' => [
            new FileMatcherPattern('/a/[a-c-e-f]/c'),
            [
                '/a/a' => false,
                '/a/-' => true,
                '/a/-/a' => true,
                '/a/c/a' => true,
                '/a/a/a' => true,
                '/a/d/a' => false,
                '/a/e/a' => true,
                '/a/g/a' => false,
                '/a/-/c' => true,
            ],
        ];

        yield 'range suffix dash' => [
            new FileMatcherPattern('/a/[a-ce-f-]/c'),
            [
                '/a/a' => false,
                '/a/-' => true,
                '/a/-/a' => true,
                '/a/c/a' => true,
                '/a/a/a' => true,
                '/a/d/a' => false,
                '/a/e/a' => true,
                '/a/g/a' => false,
                '/a/-/c' => true,
            ],
        ];

        yield 'complementation single char' => [
            new FileMatcherPattern('/a/[!a]/c'),
            [
                '/a/a' => false,
                '/a/a/b' => false,
                '/a/b/b' => true,
                '/a/0/b' => true,
                '/a/0a/b' => false,
            ]
        ];

        yield 'complementation multi char' => [
            new FileMatcherPattern('/a/[!abc]/c'),
            [
                '/a/a/b' => false,
                '/a/b/b' => false,
                '/a/c/b' => false,
                '/a/d/b' => true,
            ]
        ];

        yield 'complementation range' => [
            new FileMatcherPattern('/a/[!a-c]/c'),
            [
                '/a/a/b' => false,
                '/a/b/b' => false,
                '/a/c/b' => false,
                '/a/d/b' => true,
            ]
        ];

        yield 'escape range' => [
            new FileMatcherPattern('/a/\[!a-c]/c'),
            [
                '/a/[!a-c]/c' => true,
                '/a/[!a-c]/c/d' => true,
                '/b/[!a-c]/c/d' => false,
            ]
        ];

        // TODO: test all the character clases
        // [:alnum:]  [:alpha:]  [:blank:]  [:cntrl:]
        // [:digit:]  [:graph:]  [:lower:]  [:print:]
        // [:punct:]  [:space:]  [:upper:]  [:xdigit:]
        yield 'character class...' => [
            new FileMatcherPattern('/a/[:alnum:]/c'),
            [
                '/a/1/c' => true,
                '/a/2/c' => true,
                '/b/!/c' => false,
            ]
        ];

        // TODO: all of these?
        // Collating symbols, like "[.ch.]" or "[.a-acute.]", where the
        // string between "[." and ".]" is a collating element defined for
        // the current locale.  Note that this may be a multicharacter
        // element
        yield 'collating symbols' => [
            new FileMatcherPattern('/a/[.a-acute.]/c'),
            [
                '/a/á/c' => true,
                '/a/a/c' => false,
            ]
        ];

        // TODO: all of these?
        // Equivalence class expressions, like "[=a=]", where the string
        //        between "[=" and "=]" is any collating element from its
        //        equivalence class, as defined for the current locale.  For
        //        example, "[[=a=]]" might be equivalent to "[aáàäâ]", that is, to
        //        "[a[.a-acute.][.a-grave.][.a-umlaut.][.a-circumflex.]]".
        yield 'equivalence class expressions' => [
            new FileMatcherPattern('/a/[=a=]/c'),
            [
                '/a/á/c' => true,
                '/a/a/c' => true,
            ]

        ];
    }

    /**
     * TODO: expand this
     * @return Generator<string,array{FileMatcherPattern,array<string,bool>}>
     */
    public static function provideRelativePathSegments(): Generator
    {
        yield 'dot dot' => [
            new FileMatcherPattern('/a/../a/c'),
            [
                '/a/a/c' => true,
                '/a/b/c' => true,
            ]

        ];
    }
    /**
     * @param array<FileMatcherPattern,bool> $matchMap
     */
    private static function assertMap(FileMatcherPattern $pattern, array $matchMap): void
    {
        foreach ($matchMap as $candidate => $shouldMatch) {
            $matches = FileMatcher::match($candidate, $pattern);
            if ($matches === $shouldMatch) {
                self::assertTrue(true);
                continue;
            }
            self::fail(sprintf(
                'Expected the pattern "%s" %s match path "%s"',
                $pattern->path,
                $shouldMatch ? 'to' : 'to not',
                $candidate
            ));
        }
    }
}
