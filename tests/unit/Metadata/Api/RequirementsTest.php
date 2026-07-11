<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Api;

use const PHP_VERSION;
use function phpversion;
use function sprintf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Version;
use PHPUnit\TestFixture\RequirementsEnvironmentVariableTest;

#[CoversClass(Requirements::class)]
#[Small]
#[Group('metadata')]
#[Group('metadata/api')]
final class RequirementsTest extends TestCase
{
    /**
     * @return non-empty-list<array{string, list<string>}>
     */
    public static function missingRequirementsProvider(): array
    {
        return [
            ['testOne',            []],
            ['testNine',           [
                'Function testFunc() is required.',
            ]],
            ['testTen',            [
                'PHP extension testExt is required, but it is not loaded.',
            ]],
            ['testAlwaysSkip',     [
                self::phpunitIsRequired('>= 1111111.0.0'),
            ]],
            ['testAlwaysSkip2',    [
                self::phpIsRequired('>= 9999999.0.0'),
            ]],
            ['testAlwaysSkip3',    [
                'Operating system DOESNOTEXIST is required.',
            ]],
            ['testAllPossibleRequirements', [
                self::phpIsRequired('99.0.0-dev'),
                self::phpunitIsRequired('99.0.0-dev'),
                'Operating system DOESNOTEXIST is required.',
                'Operating system DOESNOTEXIST is required.',
                'Function testFuncOne() is required.',
                'Function testFunc2() is required.',
                'Method DoesNotExist::doesNotExist() is required.',
                'PHP extension testExtOne is required, but it is not loaded.',
                'PHP extension testExt2 is required, but it is not loaded.',
                'PHP extension testExtThree >= 2.0.0 is required, but it is not loaded.',
                'Setting "not_a_setting" is required to be "Off".',
            ]],
            ['testPHPVersionOperatorLessThan', [
                self::phpIsRequired('< 5.4.0'),
            ]],
            ['testPHPVersionOperatorLessThanEquals', [
                self::phpIsRequired('<= 5.4.0'),
            ]],
            ['testPHPVersionOperatorGreaterThan', [
                self::phpIsRequired('> 99.0.0'),
            ]],
            ['testPHPVersionOperatorGreaterThanEquals', [
                self::phpIsRequired('>= 99.0.0'),
            ]],
            ['testPHPVersionOperatorNoSpace', [
                self::phpIsRequired('>= 99.0.0'),
            ]],
            ['testPHPVersionOperatorEquals', [
                self::phpIsRequired('= 5.4.0'),
            ]],
            ['testPHPVersionOperatorDoubleEquals', [
                self::phpIsRequired('== 5.4.0'),
            ]],
            ['testPHPUnitVersionOperatorLessThan', [
                self::phpunitIsRequired('< 1.0.0'),
            ]],
            ['testPHPUnitVersionOperatorLessThanEquals', [
                self::phpunitIsRequired('<= 1.0.0'),
            ]],
            ['testPHPUnitVersionOperatorGreaterThan', [
                self::phpunitIsRequired('> 99.0.0'),
            ]],
            ['testPHPUnitVersionOperatorGreaterThanEquals', [
                self::phpunitIsRequired('>= 99.0.0'),
            ]],
            ['testPHPUnitVersionOperatorEquals', [
                self::phpunitIsRequired('= 1.0.0'),
            ]],
            ['testPHPUnitVersionOperatorDoubleEquals', [
                self::phpunitIsRequired('== 1.0.0'),
            ]],
            ['testPHPUnitVersionOperatorNoSpace', [
                self::phpunitIsRequired('>= 99.0.0'),
            ]],
            ['testExtensionVersionOperatorLessThan', [
                'PHP extension testExtOne < 1.0.0 is required, but it is not loaded.',
            ]],
            ['testExtensionVersionOperatorLessThanEquals', [
                'PHP extension testExtOne <= 1.0.0 is required, but it is not loaded.',
            ]],
            ['testExtensionVersionOperatorGreaterThan', [
                'PHP extension testExtOne > 99.0.0 is required, but it is not loaded.',
            ]],
            ['testExtensionVersionOperatorGreaterThanEquals', [
                'PHP extension testExtOne >= 99.0.0 is required, but it is not loaded.',
            ]],
            ['testExtensionVersionOperatorEquals', [
                'PHP extension testExtOne = 1.0.0 is required, but it is not loaded.',
            ]],
            ['testExtensionVersionOperatorDoubleEquals', [
                'PHP extension testExtOne == 1.0.0 is required, but it is not loaded.',
            ]],
            ['testExtensionVersionOperatorNoSpace', [
                'PHP extension testExtOne >= 99.0.0 is required, but it is not loaded.',
            ]],
            ['testLoadedExtensionVersionRequirementNotSatisfied', [
                sprintf(
                    'PHP extension spl >= 9999999.0.0 is required, but version %s is loaded.',
                    phpversion('spl'),
                ),
            ]],
            ['testVersionConstraintTildeMajor', [
                self::phpIsRequired('~1.0'),
                self::phpunitIsRequired('~2.0'),
            ]],
            ['testVersionConstraintCaretMajor', [
                self::phpIsRequired('^1.0'),
                self::phpunitIsRequired('^2.0'),
            ]],
            ['testPHPUnitExtensionRequired', [
                'PHPUnit extension "PHPUnit\TestFixture\SomeExtension" is required.',
                'PHPUnit extension "PHPUnit\TestFixture\SomeOtherExtension" is required.',
            ]],
        ];
    }

    protected function tearDown(): void
    {
        unset($_ENV['FOO'], $_ENV['BAR']);
    }

    #[DataProvider('missingRequirementsProvider')]
    public function testGetMissingRequirements(string $test, array $result): void
    {
        $this->assertEquals(
            $result,
            (new Requirements)->requirementsNotSatisfiedFor(\PHPUnit\TestFixture\RequirementsTest::class, $test),
        );
    }

    public function testGetMissingEnvironmentVariableRequirements(): void
    {
        $_ENV['FOO'] = 'foo';
        $_ENV['BAR'] = '';

        $this->assertEquals(
            [
                'Environment variable "FOO" is required to be "bar".',
                'Environment variable "BAR" is required.',
                'Environment variable "BAZ" is required.',
            ],
            (new Requirements)->requirementsNotSatisfiedFor(RequirementsEnvironmentVariableTest::class, 'testRequiresEnvironmentVariable'),
        );
    }

    private static function phpIsRequired(string $versionRequirement): string
    {
        return sprintf(
            'PHP %s is required, but PHP %s is being used.',
            $versionRequirement,
            PHP_VERSION,
        );
    }

    private static function phpunitIsRequired(string $versionRequirement): string
    {
        return sprintf(
            'PHPUnit %s is required, but PHPUnit %s is being used.',
            $versionRequirement,
            Version::id(),
        );
    }
}
