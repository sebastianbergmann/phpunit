<?php
declare(strict_types=1);

namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class IsResourceNotOfTypeTest extends TestCase
{
    /** @var IsResourceNotOfType */
    private $constraint;

    public function setUp()
    {
        parent::setUp();

        $this->constraint = new IsResourceNotOfType('xml');
    }

    public function testShouldReceiveNonResourceAndFail()
    {
        $given = 'foo';

        $this->expectException(ExpectationFailedException::class);

        $this->constraint->evaluate($given);
    }

    public function testShouldReceiveStreamOfOtherTypeAndSucceed()
    {
        $given = fopen("php://memory", 'rb');

        $result = $this->constraint->evaluate($given, '', true);

        $this->assertTrue($result);

        fclose($given);
    }

    public function testShouldReceiveAXmlResourceAndFail()
    {
        $given = \xml_parser_create();

        $this->expectException(ExpectationFailedException::class);

        $this->constraint->evaluate($given);

        fclose($given);
    }
}
