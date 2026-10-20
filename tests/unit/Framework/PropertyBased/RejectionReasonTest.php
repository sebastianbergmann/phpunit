<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(RejectionReason::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class RejectionReasonTest extends TestCase
{
    /**
     * @return array<string, array{RejectionReason, non-empty-string, non-empty-string}>
     */
    public static function reasonProvider(): array
    {
        return [
            'assumption' => [
                RejectionReason::Assumption,
                'by assume()',
                'generate the values that the property needs instead of rejecting the others',
            ],
            'filter not satisfied' => [
                RejectionReason::FilterNotSatisfied,
                'because no value that satisfies a filter was generated',
                'generate the values that the property needs instead of rejecting the others',
            ],
            'too many choices' => [
                RejectionReason::TooManyChoices,
                'because generating them required too many choices',
                'generate smaller values',
            ],
            'objects nested too deeply' => [
                RejectionReason::ObjectsNestedTooDeeply,
                'because generating them required too many nested objects',
                'generate smaller values',
            ],
            'too many duplicate elements' => [
                RejectionReason::TooManyDuplicateElements,
                'because no new element for a collection was generated',
                'generate the elements of collections from more values',
            ],
            'no character of class' => [
                RejectionReason::NoCharacterOfClass,
                'because no character that a character class matches was generated',
                'generate the values that the property needs instead of rejecting the others',
            ],
            'does not match regular expression' => [
                RejectionReason::DoesNotMatchRegularExpression,
                'because a generated string is not matched by its regular expression',
                'generate the values that the property needs instead of rejecting the others',
            ],
        ];
    }

    #[DataProvider('reasonProvider')]
    public function testDescribesWhyExamplesWereRejected(RejectionReason $reason, string $description, string $advice): void
    {
        $this->assertSame($description, $reason->description());
    }

    #[DataProvider('reasonProvider')]
    public function testAdvisesHowFewerExamplesCanBeRejected(RejectionReason $reason, string $description, string $advice): void
    {
        $this->assertSame($advice, $reason->advice());
    }
}
