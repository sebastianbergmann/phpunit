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

/**
 * Why an example of a property was rejected.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This enumeration is not covered by the backward compatibility promise for PHPUnit
 */
enum RejectionReason
{
    /**
     * Describes why examples were rejected, following "were rejected" or the
     * number of examples that were rejected for this reason.
     *
     * @return non-empty-string
     */
    public function description(): string
    {
        return match ($this) {
            self::Assumption                    => 'by assume()',
            self::FilterNotSatisfied            => 'because no value that satisfies a filter was generated',
            self::TooManyChoices                => 'because generating them required too many choices',
            self::ObjectsNestedTooDeeply        => 'because generating them required too many nested objects',
            self::TooManyDuplicateElements      => 'because no new element for a collection was generated',
            self::NoCharacterOfClass            => 'because no character that a character class matches was generated',
            self::DoesNotMatchRegularExpression => 'because a generated string is not matched by its regular expression',
        };
    }

    /**
     * How fewer examples can be rejected for this reason.
     *
     * @return non-empty-string
     */
    public function advice(): string
    {
        return match ($this) {
            self::Assumption,
            self::FilterNotSatisfied,
            self::NoCharacterOfClass,
            self::DoesNotMatchRegularExpression => 'generate the values that the property needs instead of rejecting the others',
            self::TooManyChoices,
            self::ObjectsNestedTooDeeply   => 'generate smaller values',
            self::TooManyDuplicateElements => 'generate the elements of collections from more values',
        };
    }
    case Assumption;
    case FilterNotSatisfied;
    case TooManyChoices;
    case ObjectsNestedTooDeeply;
    case TooManyDuplicateElements;
    case NoCharacterOfClass;
    case DoesNotMatchRegularExpression;
}
