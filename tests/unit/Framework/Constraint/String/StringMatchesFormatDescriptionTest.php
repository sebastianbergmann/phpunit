<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringMatchesFormatDescription::class)]
#[CoversClass(Constraint::class)]
#[Small]
final class StringMatchesFormatDescriptionTest extends TestCase
{
    public function testConstraintStringMatchesDirectorySeparator(): void
    {
        $constraint = new StringMatchesFormatDescription('*%e*');

        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertFalse($constraint->evaluate('*a*', '', true));

        $this->assertTrue($constraint->evaluate('*' . DIRECTORY_SEPARATOR . '*', '', true));
    }

    public function testConstraintStringMatchesString(): void
    {
        $constraint = new StringMatchesFormatDescription('*%s*');

        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertFalse($constraint->evaluate("*\n*", '', true));

        $this->assertTrue($constraint->evaluate('***', '', true));
        $this->assertTrue($constraint->evaluate('*foo 123 bar*', '', true));
    }

    public function testConstraintStringMatchesOptionalString(): void
    {
        $constraint = new StringMatchesFormatDescription('*%S*');

        $this->assertFalse($constraint->evaluate('*', '', true));
        $this->assertFalse($constraint->evaluate("*\n*", '', true));

        $this->assertTrue($constraint->evaluate('***', '', true));
        $this->assertTrue($constraint->evaluate('*foo 123 bar*', '', true));
        $this->assertTrue($constraint->evaluate('**', '', true));
    }

    public function testConstraintStringMatchesAnything(): void
    {
        $constraint = new StringMatchesFormatDescription('*%a*');

        $this->assertFalse($constraint->evaluate('**', '', true));

        $this->assertTrue($constraint->evaluate('***', '', true));
        $this->assertTrue($constraint->evaluate('*foo 123 bar*', '', true));
        $this->assertTrue($constraint->evaluate("*\n*", '', true));
    }

    public function testConstraintStringMatchesOptionalAnything(): void
    {
        $constraint = new StringMatchesFormatDescription('*%A*');

        $this->assertFalse($constraint->evaluate('*', '', true));

        $this->assertTrue($constraint->evaluate('***', '', true));
        $this->assertTrue($constraint->evaluate('*foo 123 bar*', '', true));
        $this->assertTrue($constraint->evaluate("*\n*", '', true));
        $this->assertTrue($constraint->evaluate('**', '', true));
    }

    public function testConstraintStringMatchesWhitespace(): void
    {
        $constraint = new StringMatchesFormatDescription('*%w*');

        $this->assertFalse($constraint->evaluate('*', '', true));
        $this->assertFalse($constraint->evaluate('*a*', '', true));

        $this->assertTrue($constraint->evaluate('* *', '', true));
        $this->assertTrue($constraint->evaluate("*\t\n*", '', true));
        $this->assertTrue($constraint->evaluate('**', '', true));
    }

    public function testConstraintStringMatchesInteger(): void
    {
        $constraint = new StringMatchesFormatDescription('*%i*');

        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertFalse($constraint->evaluate('*a*', '', true));
        $this->assertFalse($constraint->evaluate('*1.0*', '', true));

        $this->assertTrue($constraint->evaluate('*0*', '', true));
        $this->assertTrue($constraint->evaluate('*12*', '', true));
        $this->assertTrue($constraint->evaluate('*-1*', '', true));
        $this->assertTrue($constraint->evaluate('*+2*', '', true));
    }

    public function testConstraintStringMatchesUnsignedInt(): void
    {
        $constraint = new StringMatchesFormatDescription('*%d*');

        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertFalse($constraint->evaluate('*a*', '', true));
        $this->assertFalse($constraint->evaluate('*1.0*', '', true));
        $this->assertFalse($constraint->evaluate('*-1*', '', true));
        $this->assertFalse($constraint->evaluate('*+2*', '', true));

        $this->assertTrue($constraint->evaluate('*0*', '', true));
        $this->assertTrue($constraint->evaluate('*12*', '', true));
    }

    public function testConstraintStringMatchesHexadecimal(): void
    {
        $constraint = new StringMatchesFormatDescription('*%x*');

        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertFalse($constraint->evaluate('***', '', true));
        $this->assertFalse($constraint->evaluate('*g*', '', true));
        $this->assertFalse($constraint->evaluate('*1.0*', '', true));
        $this->assertFalse($constraint->evaluate('*-1*', '', true));
        $this->assertFalse($constraint->evaluate('*+2*', '', true));

        $this->assertTrue($constraint->evaluate('*0f0f0f*', '', true));
        $this->assertTrue($constraint->evaluate('*0*', '', true));
        $this->assertTrue($constraint->evaluate('*12*', '', true));
        $this->assertTrue($constraint->evaluate('*a*', '', true));
    }

    public function testConstraintStringMatchesFloat(): void
    {
        $constraint = new StringMatchesFormatDescription('*%f*');

        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertFalse($constraint->evaluate('***', '', true));
        $this->assertFalse($constraint->evaluate('*a*', '', true));
        $this->assertFalse($constraint->evaluate('*1.*', '', true));

        $this->assertTrue($constraint->evaluate('*1.0*', '', true));
        $this->assertTrue($constraint->evaluate('*0*', '', true));
        $this->assertTrue($constraint->evaluate('*12*', '', true));
        $this->assertTrue($constraint->evaluate('*.1*', '', true));
        $this->assertTrue($constraint->evaluate('*2e3*', '', true));
        $this->assertTrue($constraint->evaluate('*-2.34e-56*', '', true));
        $this->assertTrue($constraint->evaluate('*+2.34e+56*', '', true));
    }

    public function testConstraintStringMatchesCharacter(): void
    {
        $constraint = new StringMatchesFormatDescription('*%c*');

        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertFalse($constraint->evaluate('*ab*', '', true));

        $this->assertTrue($constraint->evaluate('***', '', true));
        $this->assertTrue($constraint->evaluate('*a*', '', true));
        $this->assertTrue($constraint->evaluate('*g*', '', true));
        $this->assertTrue($constraint->evaluate('*0*', '', true));
        $this->assertTrue($constraint->evaluate('*2*', '', true));
        $this->assertTrue($constraint->evaluate('* *', '', true));
        $this->assertTrue($constraint->evaluate("*\n*", '', true));
    }

    public function testConstraintStringMatchesEscapedPercent(): void
    {
        $constraint = new StringMatchesFormatDescription('%%,%%e,%%s,%%S,%%a,%%A,%%w,%%i,%%d,%%x,%%f,%%c,%%Z,%%%%,%%');

        $this->assertFalse($constraint->evaluate('%%,%' . DIRECTORY_SEPARATOR . ',%*,%*,%*,%*,% ,%0,%0,%0f0f0f,%1.0,%*,%%Z,%%%%,%%', '', true));
        $this->assertTrue($constraint->evaluate('%,%e,%s,%S,%a,%A,%w,%i,%d,%x,%f,%c,%Z,%%,%', '', true));
    }

    public function testConstraintStringMatchesEscapedPercentThenPlaceholder(): void
    {
        $constraint = new StringMatchesFormatDescription('%%%e,%%%s,%%%S,%%%a,%%%A,%%%w,%%%i,%%%d,%%%x,%%%f,%%%c');

        $this->assertFalse($constraint->evaluate('%%e,%%s,%%S,%%a,%%A,%%w,%%i,%%d,%%x,%%f,%%c', '', true));
        $this->assertTrue($constraint->evaluate('%' . DIRECTORY_SEPARATOR . ',%*,%*,%*,%*,% ,%0,%0,%0f0f0f,%1.0,%*', '', true));
    }

    public function testConstraintStringMatchesSlash(): void
    {
        $constraint = new StringMatchesFormatDescription('/');

        $this->assertFalse($constraint->evaluate('\\/', '', true));
        $this->assertTrue($constraint->evaluate('/', '', true));
    }

    public function testConstraintStringMatchesBackslash(): void
    {
        $constraint = new StringMatchesFormatDescription('\\');

        $this->assertFalse($constraint->evaluate('\\\\', '', true));
        $this->assertTrue($constraint->evaluate('\\', '', true));
    }

    public function testConstraintStringMatchesBackslashSlash(): void
    {
        $constraint = new StringMatchesFormatDescription('\\/');

        $this->assertFalse($constraint->evaluate('/', '', true));
        $this->assertTrue($constraint->evaluate('\\/', '', true));
    }

    public function testConstraintStringMatchesNewline(): void
    {
        $constraint = new StringMatchesFormatDescription("\r\n");

        $this->assertFalse($constraint->evaluate("*\r\n", '', true));
        $this->assertTrue($constraint->evaluate("\r\n", '', true));
    }

    public function testConstraintStringMatchesAnythingMultiline(): void
    {
        $constraint = new StringMatchesFormatDescription("*\n%a\nbar\nbaz");

        $this->assertFalse($constraint->evaluate("*\n*", '', true));
    }

    public function testFailureMessageWithNewlines(): void
    {
        $constraint = new StringMatchesFormatDescription("%c\nfoo\n%c");

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            <<<'EOD'
Failed asserting that string matches format description.
--- Expected
+++ Actual
@@ @@
 *
-foo
+bar
 *

EOD
        );

        $constraint->evaluate("*\nbar\n*");
    }

    public function testFailureMessageWithNewlinesAndAnythingMatcher(): void
    {
        $constraint = new StringMatchesFormatDescription("%a\nfoo\n%s\nbar");

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            <<<'EOD'
Failed asserting that string matches format description.
--- Expected
+++ Actual
@@ @@
 *
 foo
 *
-bar
+mismatch

EOD
        );

        $constraint->evaluate("*\nfoo\n*\nmismatch");
    }

    public function testFailureMessageWithNewlinesAndAnythingMatcherMultilineMatches(): void
    {
        $constraint = new StringMatchesFormatDescription(
            <<<'EOD'
## before first A
%A
## after first A
*
## before second A
%A
## after second A
*
Foo: %s

EOD
        );

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            <<<'EOD'
Failed asserting that string matches format description.
--- Expected
+++ Actual
@@ @@
 ## before first A
 some multiline
+text for
+A to match
 ## after first A
 *
 ## before second A
+more multiline text
+for A to match
+## after second A
 *
-## after second A
+Foo: s match
 *
-Foo: %s
+Additional Text that is not matched

EOD
        );

        $constraint->evaluate(
            <<<'EOD'
## before first A
some multiline
text for
A to match
## after first A
*
## before second A
more multiline text
for A to match
## after second A
*
Foo: s match
*
Additional Text that is not matched
EOD
        );
    }

    public function testCanBeRepresentedAsString(): void
    {
        $this->assertSame(
            'matches format description:' . PHP_EOL . 'string',
            (new StringMatchesFormatDescription('string'))->toString(),
        );
    }

    public function testIsCountable(): void
    {
        $this->assertCount(1, (new StringMatchesFormatDescription('string')));
    }
}
