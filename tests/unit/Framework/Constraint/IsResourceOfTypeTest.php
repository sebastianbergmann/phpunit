<?php
declare(strict_types=1);

namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class IsResourceOfTypeTest extends TestCase
{
    /** @var IsResourceOfType */
    private $constraint;

    public function setUp()
    {
        parent::setUp();

        $this->constraint = new IsResourceOfType('stream');
    }

    public function testShouldReceiveNonResourceAndFail()
    {
        $given = 'foo';

        $this->expectException(ExpectationFailedException::class);

        $this->constraint->evaluate($given);
    }

    public function testShouldReceiveStreamAndSucceed()
    {
        $given = fopen("php://memory", 'rb');

        $result = $this->constraint->evaluate($given, '', true);

        $this->assertTrue($result);

        fclose($given);
    }

    public function testShouldReceiveNotAStreamAndFail()
    {
        $constraint = new IsResourceOfType('foo');
        $given = fopen("php://memory", 'rb');

        $this->expectException(ExpectationFailedException::class);

        $constraint->evaluate($given);

        fclose($given);
    }
}
