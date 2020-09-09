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

/**
 * @small
 */
final class LogicalNotTest extends UnaryOperatorTestCase
{
    public static function getOperatorName(): string
    {
        return 'not';
    }

    public static function getOperatorPrecedence(): int
    {
        return 5;
    }

    public function providerToStringWithNativeTransformations()
    {
        return $this->providerNegate();
    }

    public function evaluateExpectedResult(bool $input): bool
    {
        return !$input;
    }

    public function providerNegate()
    {
        return [
            ['ocean contains water', 'ocean does not contain water'],
            [
                '\'this is water\' contains "water" and contains "is"',
                '\'this is water\' does not contain "water" and does not contain "is"',
            ],
            ['what it contains', 'what it contains'],
            ['life exists in outer space', 'life does not exist in outer space'],
            ['alien exists', 'alien does not exist'],
            ['it coexists', 'it coexists'],
            ['the dog has a bone', 'the dog does not have a bone'],
            ['whatever it has', 'whatever it has'],
            ['apple is red', 'apple is not red'],
            ['yes, it is', 'yes, it is'],
            ['this is clock', 'this is not clock'],
            ['how are you?', 'how are not you?'],
            ['how dare you!', 'how dare you!'],
            ['what they are', 'what they are'],
            ['that matches my preferences', 'that does not match my preferences'],
            ['dinner starts with desert', 'dinner starts not with desert'],
            ['it starts with', 'it starts with'],
            ['dinner ends with desert', 'dinner ends not with desert'],
            ['it ends with', 'it ends with'],
            ['you reference me', 'you don\'t reference me'],
            ['it\'s not not false', 'it\'s not false'],
        ];
    }

    /**
     * @dataProvider providerNegate
     */
    public function testNegate(string $input, string $expected): void
    {
        $this->assertSame($expected, LogicalNot::negate($input));
    }
}
