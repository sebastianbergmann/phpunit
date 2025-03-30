<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Attributes\RequiresFunction;
use PHPUnit\Framework\Attributes\RequiresMethod;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\RequiresPhpunit;
use PHPUnit\Framework\Attributes\RequiresPhpunitExtension;
use PHPUnit\Framework\Attributes\RequiresSetting;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\Ticket;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class RequirementsTest extends TestCase
{
    public function testOne(): void
    {
    }

    #[RequiresPhpunit('1.0')]
    public function testTwo(): void
    {
    }

    #[RequiresPhp('2.0')]
    public function testThree(): void
    {
    }

    #[RequiresPhpunit('2.0')]
    #[RequiresPhp('1.0')]
    public function testFour(): void
    {
    }

    #[RequiresPhp('5.4.0RC6')]
    public function testFive(): void
    {
    }

    #[RequiresPhp('5.4.0-alpha1')]
    public function testSix(): void
    {
    }

    #[RequiresPhp('5.4.0beta2')]
    public function testSeven(): void
    {
    }

    #[RequiresPhp('5.4-dev')]
    public function testEight(): void
    {
    }

    #[RequiresFunction('testFunc')]
    public function testNine(): void
    {
    }

    #[RequiresFunction('testFunc2')]
    #[Ticket('https://github.com/sebastianbergmann/phpunit/issues/3459')]
    public function testRequiresFunctionWithDigit(): void
    {
    }

    #[RequiresPhpExtension('testExt')]
    public function testTen(): void
    {
    }

    #[RequiresOperatingSystem('SunOS')]
    #[RequiresOperatingSystemFamily('Solaris')]
    public function testEleven(): void
    {
    }

    #[RequiresPhp('99-dev')]
    #[RequiresPhpunit('99-dev')]
    #[RequiresOperatingSystem('DOESNOTEXIST')]
    #[RequiresOperatingSystemFamily('DOESNOTEXIST')]
    #[RequiresFunction('testFuncOne')]
    #[RequiresFunction('testFunc2')]
    #[RequiresMethod('DoesNotExist', 'doesNotExist')]
    #[RequiresPhpExtension('testExtOne')]
    #[RequiresPhpExtension('testExt2')]
    #[RequiresPhpExtension('testExtThree', '2.0')]
    #[RequiresSetting('not_a_setting', 'Off')]
    public function testAllPossibleRequirements(): void
    {
    }

    #[RequiresFunction('array_merge')]
    public function testExistingFunction(): void
    {
    }

    #[RequiresMethod(ReflectionMethod::class, 'setAccessible')]
    public function testExistingMethod(): void
    {
    }

    #[RequiresPhpExtension('spl')]
    public function testExistingExtension(): void
    {
    }

    #[RequiresOperatingSystem('.*')]
    public function testExistingOs(): void
    {
    }

    #[RequiresPhpunit('1111111')]
    public function testAlwaysSkip(): void
    {
    }

    #[RequiresPhp('9999999')]
    public function testAlwaysSkip2(): void
    {
    }

    #[RequiresOperatingSystem('DOESNOTEXIST')]
    public function testAlwaysSkip3(): void
    {
    }

    #[RequiresOperatingSystemFamily('DOESNOTEXIST')]
    public function testAlwaysSkip4(): void
    {
    }

    #[RequiresPhpExtension('spl')]
    #[RequiresOperatingSystem('.*')]
    public function testSpace(): void
    {
    }

    #[RequiresPhpExtension('testExt', '1.8.0')]
    public function testSpecificExtensionVersion(): void
    {
    }

    #[TestDox('PHP version operator less than')]
    #[RequiresPhp('< 5.4')]
    public function testPHPVersionOperatorLessThan(): void
    {
    }

    #[TestDox('PHP version operator less than or equals')]
    #[RequiresPhp('<= 5.4')]
    public function testPHPVersionOperatorLessThanEquals(): void
    {
    }

    #[TestDox('PHP version operator greater than')]
    #[RequiresPhp('> 99')]
    public function testPHPVersionOperatorGreaterThan(): void
    {
    }

    #[TestDox('PHP version operator greater than or equals')]
    #[RequiresPhp('>= 99')]
    public function testPHPVersionOperatorGreaterThanEquals(): void
    {
    }

    #[TestDox('PHP version operator equals')]
    #[RequiresPhp('= 5.4')]
    public function testPHPVersionOperatorEquals(): void
    {
    }

    #[TestDox('PHP version operator double equals')]
    #[RequiresPhp('== 5.4')]
    public function testPHPVersionOperatorDoubleEquals(): void
    {
    }

    #[TestDox('PHP version operator bang equals')]
    #[RequiresPhp('!= 99')]
    public function testPHPVersionOperatorBangEquals(): void
    {
    }

    #[TestDox('PHP version operator not equals')]
    #[RequiresPhp('<> 99')]
    public function testPHPVersionOperatorNotEquals(): void
    {
    }

    #[TestDox('PHP version operator no space')]
    #[RequiresPhp('>=99')]
    public function testPHPVersionOperatorNoSpace(): void
    {
    }

    #[TestDox('PHPUnit version operator less than')]
    #[RequiresPhpunit('< 1.0')]
    public function testPHPUnitVersionOperatorLessThan(): void
    {
    }

    #[TestDox('PHPUnit version operator less than equals')]
    #[RequiresPhpunit('<= 1.0')]
    public function testPHPUnitVersionOperatorLessThanEquals(): void
    {
    }

    #[TestDox('PHPUnit version operator greater than')]
    #[RequiresPhpunit('> 99')]
    public function testPHPUnitVersionOperatorGreaterThan(): void
    {
    }

    #[TestDox('PHPUnit version operator greater than or equals')]
    #[RequiresPhpunit('>= 99')]
    public function testPHPUnitVersionOperatorGreaterThanEquals(): void
    {
    }

    #[TestDox('PHPUnit version operator equals')]
    #[RequiresPhpunit('= 1.0')]
    public function testPHPUnitVersionOperatorEquals(): void
    {
    }

    #[TestDox('PHPUnit version operator double equals')]
    #[RequiresPhpunit('== 1.0')]
    public function testPHPUnitVersionOperatorDoubleEquals(): void
    {
    }

    #[TestDox('PHPUnit version operator bang equals')]
    #[RequiresPhpunit('!= 99')]
    public function testPHPUnitVersionOperatorBangEquals(): void
    {
    }

    #[TestDox('PHPUnit version operator not equals')]
    #[RequiresPhpunit('<> 99')]
    public function testPHPUnitVersionOperatorNotEquals(): void
    {
    }

    #[TestDox('PHPUnit version operator no space')]
    #[RequiresPhpunit('>=99')]
    public function testPHPUnitVersionOperatorNoSpace(): void
    {
    }

    #[RequiresPhpExtension('testExtOne', '< 1.0')]
    public function testExtensionVersionOperatorLessThan(): void
    {
    }

    #[RequiresPhpExtension('testExtOne', '<= 1.0')]
    public function testExtensionVersionOperatorLessThanEquals(): void
    {
    }

    #[RequiresPhpExtension('testExtOne', '> 99')]
    public function testExtensionVersionOperatorGreaterThan(): void
    {
    }

    #[RequiresPhpExtension('testExtOne', '>= 99')]
    public function testExtensionVersionOperatorGreaterThanEquals(): void
    {
    }

    #[RequiresPhpExtension('testExtOne', '= 1.0')]
    public function testExtensionVersionOperatorEquals(): void
    {
    }

    #[RequiresPhpExtension('testExtOne', '== 1.0')]
    public function testExtensionVersionOperatorDoubleEquals(): void
    {
    }

    #[RequiresPhpExtension('testExtOne', '!= 99')]
    public function testExtensionVersionOperatorBangEquals(): void
    {
    }

    #[RequiresPhpExtension('testExtOne', '<> 99')]
    public function testExtensionVersionOperatorNotEquals(): void
    {
    }

    #[RequiresPhpExtension('testExtOne', '>= 99')]
    public function testExtensionVersionOperatorNoSpace(): void
    {
    }

    #[RequiresPhp('~1.0')]
    #[RequiresPhpunit('~2.0')]
    public function testVersionConstraintTildeMajor(): void
    {
    }

    #[RequiresPhp('^1.0')]
    #[RequiresPhpunit('^2.0')]
    public function testVersionConstraintCaretMajor(): void
    {
    }

    #[RequiresPhp('~3.4.7')]
    #[RequiresPhpunit('~4.7.1')]
    public function testVersionConstraintTildeMinor(): void
    {
    }

    #[RequiresPhp('^7.0.17')]
    #[RequiresPhpunit('^4.7.1')]
    public function testVersionConstraintCaretMinor(): void
    {
    }

    #[RequiresPhp('^5.6 || ^7.0')]
    #[RequiresPhpunit('^5.0 || ^6.0')]
    public function testVersionConstraintCaretOr(): void
    {
    }

    #[RequiresPhp('~5.6.22 || ~7.0.17')]
    #[RequiresPhpunit('~5.0.5 || ~6.0.6')]
    public function testVersionConstraintTildeOr(): void
    {
    }

    #[RequiresPhp('~5.6.22 || ^7.0')]
    #[RequiresPhpunit('~5.6.22 || ^7.0')]
    public function testVersionConstraintTildeOrCaret(): void
    {
    }

    #[RequiresPhp('^5.6 || ~7.0.17')]
    #[RequiresPhpunit('^5.6 || ~7.0.17')]
    public function testVersionConstraintCaretOrTilde(): void
    {
    }

    #[RequiresPhp('~5.6.22 || ~7.0.17')]
    #[RequiresPhpunit('~5.6.22 || ~7.0.17')]
    public function testVersionConstraintRegexpIgnoresWhitespace(): void
    {
    }

    #[RequiresSetting('display_errors', 'On')]
    public function testSettingDisplayErrorsOn(): void
    {
    }

    #[RequiresPhpunitExtension(SomeExtension::class)]
    #[RequiresPhpunitExtension(SomeOtherExtension::class)]
    public function testPHPUnitExtensionRequired(): void
    {
    }
}
