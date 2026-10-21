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

#[CoversClass(ChoiceSequence::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class ChoiceSequenceTest extends TestCase
{
    /**
     * @return array<string, array{bool, list<non-negative-int>, list<non-negative-int>}>
     */
    public static function shortlexProvider(): array
    {
        return [
            'shorter sequence'                                   => [true, [9], [0, 0]],
            'longer sequence'                                    => [false, [0, 0], [9]],
            'empty sequence'                                     => [true, [], [0]],
            'lexicographically smaller sequence of equal length' => [true, [1, 2, 3], [1, 3, 0]],
            'lexicographically larger sequence of equal length'  => [false, [1, 3, 0], [1, 2, 3]],
            'equal sequence'                                     => [false, [1, 2, 3], [1, 2, 3]],
        ];
    }

    /**
     * @param list<non-negative-int> $choices
     * @param list<non-negative-int> $otherChoices
     */
    #[DataProvider('shortlexProvider')]
    public function testChoiceSequencesAreOrderedShortlex(bool $expected, array $choices, array $otherChoices): void
    {
        $this->assertSame($expected, new ChoiceSequence($choices)->isSimplerThan(new ChoiceSequence($otherChoices)));
    }

    public function testHasTheLargestChoiceThatEachChoiceCouldHaveBeen(): void
    {
        $sequence = new ChoiceSequence([3, 0], [], [10, 1]);

        $this->assertSame(10, $sequence->maximumAt(0));
        $this->assertSame(1, $sequence->maximumAt(1));
        $this->assertNull($sequence->maximumAt(2));
    }

    public function testTheLargestChoiceThatAChoiceCouldHaveBeenIsNotKnownForASequenceThatWasNotDrawnByAChoiceSource(): void
    {
        $this->assertNull(new ChoiceSequence([3, 0])->maximumAt(0));
    }
}
