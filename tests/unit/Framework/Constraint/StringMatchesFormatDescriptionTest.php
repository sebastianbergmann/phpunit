<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework\Constraint;

class StringMatchesFormatDescriptionTest extends ConstraintTestCase
{
    public function testConstraintStringMatches()
    {
        $constraint = new StringMatchesFormatDescription('*%c*');

        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertTrue($constraint->evaluate('***', '', true));
        $this->assertEquals('matches PCRE pattern "/^\*.\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }

    public function testConstraintStringMatches2()
    {
        $constraint = new StringMatchesFormatDescription('*%s*');

        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertTrue($constraint->evaluate('***', '', true));
        $this->assertEquals('matches PCRE pattern "/^\*[^\r\n]+\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }

    public function testConstraintStringMatches3()
    {
        $constraint = new StringMatchesFormatDescription('*%i*');

        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertTrue($constraint->evaluate('*0*', '', true));
        $this->assertEquals('matches PCRE pattern "/^\*[+-]?\d+\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }

    public function testConstraintStringMatches4()
    {
        $constraint = new StringMatchesFormatDescription('*%d*');

        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertTrue($constraint->evaluate('*0*', '', true));
        $this->assertEquals('matches PCRE pattern "/^\*\d+\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }

    public function testConstraintStringMatches5()
    {
        $constraint = new StringMatchesFormatDescription('*%x*');

        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertTrue($constraint->evaluate('*0f0f0f*', '', true));
        $this->assertEquals('matches PCRE pattern "/^\*[0-9a-fA-F]+\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }

    public function testConstraintStringMatches6()
    {
        $constraint = new StringMatchesFormatDescription('*%f*');

        $this->assertFalse($constraint->evaluate('**', '', true));
        $this->assertTrue($constraint->evaluate('*1.0*', '', true));
        $this->assertEquals('matches PCRE pattern "/^\*[+-]?\.?\d+\.?\d*(?:[Ee][+-]?\d+)?\*$/s"', $constraint->toString());
        $this->assertCount(1, $constraint);
    }
}
