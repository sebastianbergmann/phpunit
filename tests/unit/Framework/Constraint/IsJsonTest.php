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

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;

/**
 * @small
 */
final class IsJsonTest extends ConstraintTestCase
{
    public static function evaluateDataprovider(): array
    {
        return [
            'valid JSON'                                     => [true, '{}'],
            'empty string should be treated as invalid JSON' => [false, ''],
        ];
    }

    /**
     * @testdox Evaluate $_dataName
     * @dataProvider evaluateDataprovider
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testEvaluate($expected, $jsonOther): void
    {
        $constraint = new IsJson;

        $this->assertEquals($expected, $constraint->evaluate($jsonOther, '', true));
    }

    public function testIsJsonCanBeExportedAsString(): void
    {
        $isJson = new IsJson;

        $this->assertSame('is valid JSON', $isJson->toString());
    }

    public function testIsJsonCanBeEmptyString(): void
    {
        $isJson = new IsJson;

        try {
            $isJson->evaluate('');
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that an empty string is valid JSON.

EOF
                ,
                TestFailure::exceptionToString($e)
            );
        }
    }
}
