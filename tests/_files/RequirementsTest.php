<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class RequirementsTest extends TestCase
{
    public function testOne(): void
    {
    }

    /**
     * @requires PHPUnit 1.0
     */
    public function testTwo(): void
    {
    }

    /**
     * @requires PHP 2.0
     */
    public function testThree(): void
    {
    }

    /**
     * @requires PHPUnit 2.0
     * @requires PHP 1.0
     */
    public function testFour(): void
    {
    }

    /**
     * @requires PHP 5.4.0RC6
     */
    public function testFive(): void
    {
    }

    /**
     * @requires PHP 5.4.0-alpha1
     */
    public function testSix(): void
    {
    }

    /**
     * @requires PHP 5.4.0beta2
     */
    public function testSeven(): void
    {
    }

    /**
     * @requires PHP 5.4-dev
     */
    public function testEight(): void
    {
    }

    /**
     * @requires function testFunc
     */
    public function testNine(): void
    {
    }

    /**
     * @requires function testFunc2
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/3459
     */
    public function testRequiresFunctionWithDigit(): void
    {
    }

    /**
     * @requires extension testExt
     */
    public function testTen(): void
    {
    }

    /**
     * @requires OS SunOS
     * @requires OSFAMILY Solaris
     */
    public function testEleven(): void
    {
    }

    /**
     * @requires PHP 99-dev
     * @requires PHPUnit 9-dev
     * @requires OS DOESNOTEXIST
     * @requires function testFuncOne
     * @requires function testFunc2
     * @requires extension testExtOne
     * @requires extension testExt2
     * @requires extension testExtThree 2.0
     * @requires setting not_a_setting Off
     */
    public function testAllPossibleRequirements(): void
    {
    }

    /**
     * @requires function array_merge
     */
    public function testExistingFunction(): void
    {
    }

    /**
     * @requires function ReflectionMethod::setAccessible
     */
    public function testExistingMethod(): void
    {
    }

    /**
     * @requires extension spl
     */
    public function testExistingExtension(): void
    {
    }

    /**
     * @requires OS .*
     */
    public function testExistingOs(): void
    {
    }

    /**
     * @requires PHPUnit 1111111
     */
    public function testAlwaysSkip(): void
    {
    }

    /**
     * @requires PHP 9999999
     */
    public function testAlwaysSkip2(): void
    {
    }

    /**
     * @requires OS DOESNOTEXIST
     */
    public function testAlwaysSkip3(): void
    {
    }

    /**
     * @requires OSFAMILY DOESNOTEXIST
     */
    public function testAlwaysSkip4(): void
    {
    }

    /**
     * @requires extension spl
     * @requires OS .*
     */
    public function testSpace(): void
    {
    }

    /**
     * @requires extension testExt 1.8.0
     */
    public function testSpecificExtensionVersion(): void
    {
    }

    /**
     * @requires PHP < 5.4
     */
    public function testPHPVersionOperatorLessThan(): void
    {
    }

    /**
     * @requires PHP <= 5.4
     */
    public function testPHPVersionOperatorLessThanEquals(): void
    {
    }

    /**
     * @requires PHP > 99
     */
    public function testPHPVersionOperatorGreaterThan(): void
    {
    }

    /**
     * @requires PHP >= 99
     */
    public function testPHPVersionOperatorGreaterThanEquals(): void
    {
    }

    /**
     * @requires PHP = 5.4
     */
    public function testPHPVersionOperatorEquals(): void
    {
    }

    /**
     * @requires PHP == 5.4
     */
    public function testPHPVersionOperatorDoubleEquals(): void
    {
    }

    /**
     * @requires PHP != 99
     */
    public function testPHPVersionOperatorBangEquals(): void
    {
    }

    /**
     * @requires PHP <> 99
     */
    public function testPHPVersionOperatorNotEquals(): void
    {
    }

    /**
     * @requires PHP >=99
     */
    public function testPHPVersionOperatorNoSpace(): void
    {
    }

    /**
     * @requires PHPUnit < 1.0
     */
    public function testPHPUnitVersionOperatorLessThan(): void
    {
    }

    /**
     * @requires PHPUnit <= 1.0
     */
    public function testPHPUnitVersionOperatorLessThanEquals(): void
    {
    }

    /**
     * @requires PHPUnit > 99
     */
    public function testPHPUnitVersionOperatorGreaterThan(): void
    {
    }

    /**
     * @requires PHPUnit >= 99
     */
    public function testPHPUnitVersionOperatorGreaterThanEquals(): void
    {
    }

    /**
     * @requires PHPUnit = 1.0
     */
    public function testPHPUnitVersionOperatorEquals(): void
    {
    }

    /**
     * @requires PHPUnit == 1.0
     */
    public function testPHPUnitVersionOperatorDoubleEquals(): void
    {
    }

    /**
     * @requires PHPUnit != 99
     */
    public function testPHPUnitVersionOperatorBangEquals(): void
    {
    }

    /**
     * @requires PHPUnit <> 99
     */
    public function testPHPUnitVersionOperatorNotEquals(): void
    {
    }

    /**
     * @requires PHPUnit >=99
     */
    public function testPHPUnitVersionOperatorNoSpace(): void
    {
    }

    /**
     * @requires extension testExtOne < 1.0
     */
    public function testExtensionVersionOperatorLessThan(): void
    {
    }

    /**
     * @requires extension testExtOne <= 1.0
     */
    public function testExtensionVersionOperatorLessThanEquals(): void
    {
    }

    /**
     * @requires extension testExtOne > 99
     */
    public function testExtensionVersionOperatorGreaterThan(): void
    {
    }

    /**
     * @requires extension testExtOne >= 99
     */
    public function testExtensionVersionOperatorGreaterThanEquals(): void
    {
    }

    /**
     * @requires extension testExtOne = 1.0
     */
    public function testExtensionVersionOperatorEquals(): void
    {
    }

    /**
     * @requires extension testExtOne == 1.0
     */
    public function testExtensionVersionOperatorDoubleEquals(): void
    {
    }

    /**
     * @requires extension testExtOne != 99
     */
    public function testExtensionVersionOperatorBangEquals(): void
    {
    }

    /**
     * @requires extension testExtOne <> 99
     */
    public function testExtensionVersionOperatorNotEquals(): void
    {
    }

    /**
     * @requires extension testExtOne >=99
     */
    public function testExtensionVersionOperatorNoSpace(): void
    {
    }

    /**
     * @requires PHP ~1.0
     * @requires PHPUnit ~2.0
     */
    public function testVersionConstraintTildeMajor(): void
    {
    }

    /**
     * @requires PHP ^1.0
     * @requires PHPUnit ^2.0
     */
    public function testVersionConstraintCaretMajor(): void
    {
    }

    /**
     * @requires PHP ~3.4.7
     * @requires PHPUnit ~4.7.1
     */
    public function testVersionConstraintTildeMinor(): void
    {
    }

    /**
     * @requires PHP ^7.0.17
     * @requires PHPUnit ^4.7.1
     */
    public function testVersionConstraintCaretMinor(): void
    {
    }

    /**
     * @requires PHP ^5.6 || ^7.0
     * @requires PHPUnit ^5.0 || ^6.0
     */
    public function testVersionConstraintCaretOr(): void
    {
    }

    /**
     * @requires PHP ~5.6.22 || ~7.0.17
     * @requires PHPUnit ^5.0.5 || ^6.0.6
     */
    public function testVersionConstraintTildeOr(): void
    {
    }

    /**
     * @requires PHP ~5.6.22 || ^7.0
     * @requires PHPUnit ~5.6.22 || ^7.0
     */
    public function testVersionConstraintTildeOrCaret(): void
    {
    }

    /**
     * @requires PHP ^5.6 || ~7.0.17
     * @requires PHPUnit ^5.6 || ~7.0.17
     */
    public function testVersionConstraintCaretOrTilde(): void
    {
    }

    /**
     * @requires   PHP        ~5.6.22 || ~7.0.17
     * @requires   PHPUnit    ~5.6.22 || ~7.0.17
     */
    public function testVersionConstraintRegexpIgnoresWhitespace(): void
    {
    }

    /**
     * @requires   PHP ~^12345
     */
    public function testVersionConstraintInvalidPhpConstraint(): void
    {
    }

    /**
     * @requires   PHPUnit ~^12345
     */
    public function testVersionConstraintInvalidPhpUnitConstraint(): void
    {
    }

    /**
     * @requires setting display_errors On
     */
    public function testSettingDisplayErrorsOn(): void
    {
    }
}
