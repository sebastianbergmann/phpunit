<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata;

use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\RequirementsTest;
use PHPUnit\Util\Test;

/**
 * @small
 */
final class RequirementsFacadeTest extends TestCase
{
    /**
     * @testdox Test::getMissingRequirements() for $test
     * @dataProvider missingRequirementsProvider
     */
    public function testGetMissingRequirements($test, $result): void
    {
        $this->assertEquals(
            $result,
            (new RequirementsFacade)->requirementsNotSatisfiedFor(RequirementsTest::class, $test)
        );
    }

    public function missingRequirementsProvider(): array
    {
        return [
            ['testOne',            []],
            ['testNine',           [
                'Function testFunc() is required.',
            ]],
            ['testTen',            [
                'PHP extension testExt is required.',
            ]],
            ['testAlwaysSkip',     [
                'PHPUnit >= 1111111 is required.',
            ]],
            ['testAlwaysSkip2',    [
                'PHP >= 9999999 is required.',
            ]],
            ['testAlwaysSkip3',    [
                'Operating system DOESNOTEXIST is required.',
            ]],
            ['testAllPossibleRequirements', [
                'PHP >= 99-dev is required.',
                'PHP extension testExtOne is required.',
                'PHP extension testExt2 is required.',
                'PHP extension testExtThree >= 2.0 is required.',
                'PHPUnit >= 99-dev is required.',
                'Operating system DOESNOTEXIST is required.',
                'Function testFuncOne() is required.',
                'Function testFunc2() is required.',
                'Setting "not_a_setting" is required to be "Off".',
            ]],
            ['testPHPVersionOperatorLessThan', [
                'PHP < 5.4 is required.',
            ]],
            ['testPHPVersionOperatorLessThanEquals', [
                'PHP <= 5.4 is required.',
            ]],
            ['testPHPVersionOperatorGreaterThan', [
                'PHP > 99 is required.',
            ]],
            ['testPHPVersionOperatorGreaterThanEquals', [
                'PHP >= 99 is required.',
            ]],
            ['testPHPVersionOperatorNoSpace', [
                'PHP >= 99 is required.',
            ]],
            ['testPHPVersionOperatorEquals', [
                'PHP = 5.4 is required.',
            ]],
            ['testPHPVersionOperatorDoubleEquals', [
                'PHP == 5.4 is required.',
            ]],
            ['testPHPUnitVersionOperatorLessThan', [
                'PHPUnit < 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorLessThanEquals', [
                'PHPUnit <= 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorGreaterThan', [
                'PHPUnit > 99 is required.',
            ]],
            ['testPHPUnitVersionOperatorGreaterThanEquals', [
                'PHPUnit >= 99 is required.',
            ]],
            ['testPHPUnitVersionOperatorEquals', [
                'PHPUnit = 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorDoubleEquals', [
                'PHPUnit == 1.0 is required.',
            ]],
            ['testPHPUnitVersionOperatorNoSpace', [
                'PHPUnit >= 99 is required.',
            ]],
            ['testExtensionVersionOperatorLessThan', [
                'PHP extension testExtOne < 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorLessThanEquals', [
                'PHP extension testExtOne <= 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorGreaterThan', [
                'PHP extension testExtOne > 99 is required.',
            ]],
            ['testExtensionVersionOperatorGreaterThanEquals', [
                'PHP extension testExtOne >= 99 is required.',
            ]],
            ['testExtensionVersionOperatorEquals', [
                'PHP extension testExtOne = 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorDoubleEquals', [
                'PHP extension testExtOne == 1.0 is required.',
            ]],
            ['testExtensionVersionOperatorNoSpace', [
                'PHP extension testExtOne >= 99 is required.',
            ]],
            ['testVersionConstraintTildeMajor', [
                'PHP ~1.0 is required.',
                'PHPUnit ~2.0 is required.',
            ]],
            ['testVersionConstraintCaretMajor', [
                'PHP ^1.0 is required.',
                'PHPUnit ^2.0 is required.',
            ]],
        ];
    }
}
