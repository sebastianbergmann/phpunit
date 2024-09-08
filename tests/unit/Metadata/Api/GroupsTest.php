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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\LargeGroupAttributesTest;
use PHPUnit\TestFixture\MediumGroupAttributesTest;
use PHPUnit\TestFixture\NoGroupsMetadataTest;
use PHPUnit\TestFixture\SmallGroupAnnotationsTest;
use PHPUnit\TestFixture\SmallGroupAttributesTest;

#[CoversClass(Groups::class)]
#[Small]
#[Group('metadata')]
final class GroupsTest extends TestCase
{
    public static function provider(): array
    {
        return [
            [
                [
                ],
                NoGroupsMetadataTest::class,
                'testOne',
                false,
            ],

            [
                [
                    'the-group',
                    'the-ticket',
                    'small',
                    'another-group',
                    'another-ticket',
                ],
                SmallGroupAttributesTest::class,
                'testOne',
                false,
            ],

            [
                [
                    'the-group',
                    'the-ticket',
                    'small',
                    'another-group',
                    'another-ticket',
                ],
                SmallGroupAnnotationsTest::class,
                'testOne',
                false,
            ],

            [
                [
                    'the-group',
                    'the-ticket',
                    'small',
                    'another-group',
                    'another-ticket',
                    '__phpunit_covers_phpunit\testfixture\coveredclass',
                    '__phpunit_uses_phpunit\testfixture\coveredclass',
                ],
                SmallGroupAttributesTest::class,
                'testOne',
                true,
            ],

            [
                [
                    'the-group',
                    'the-ticket',
                    'small',
                    'another-group',
                    'another-ticket',
                    '__phpunit_covers_phpunit\testfixture\coveredclass',
                    '__phpunit_uses_phpunit\testfixture\coveredclass',
                ],
                SmallGroupAnnotationsTest::class,
                'testOne',
                true,
            ],

            [
                [
                    'medium',
                ],
                MediumGroupAttributesTest::class,
                'testOne',
                false,
            ],

            [
                [
                    'large',
                ],
                LargeGroupAttributesTest::class,
                'testOne',
                false,
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testAssignsGroups(array $expected, string $className, string $methodName, bool $includeVirtual): void
    {
        $this->assertSame(
            $expected,
            (new Groups)->groups($className, $methodName, $includeVirtual),
        );
    }

    public function testAssignsSize(): void
    {
        $this->assertTrue((new Groups)->size(SmallGroupAttributesTest::class, 'testOne')->isSmall());
        $this->assertTrue((new Groups)->size(MediumGroupAttributesTest::class, 'testOne')->isMedium());
        $this->assertTrue((new Groups)->size(LargeGroupAttributesTest::class, 'testOne')->isLarge());
        $this->assertTrue((new Groups)->size(NoGroupsMetadataTest::class, 'testOne')->isUnknown());
    }
}
