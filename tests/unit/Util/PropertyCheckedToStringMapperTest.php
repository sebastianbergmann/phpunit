<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Test\PropertyChecked;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(PropertyCheckedToStringMapper::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
#[TestDox('PropertyCheckedToStringMapper')]
final class PropertyCheckedToStringMapperTest extends AbstractEventTestCase
{
    public function testDescribesHowManyExamplesWereCheckedAndRejected(): void
    {
        $this->assertSame(
            "100 examples checked, 3 rejected\n",
            PropertyCheckedToStringMapper::map($this->event(100, 3, [])),
        );
    }

    public function testUsesTheSingularForOneExample(): void
    {
        $this->assertSame(
            "1 example checked, 0 rejected\n",
            PropertyCheckedToStringMapper::map($this->event(1, 0, [])),
        );
    }

    public function testDescribesThePercentageOfTheExamplesThatWereClassifiedWithEachLabelFromTheLargestToTheSmallest(): void
    {
        $this->assertStringEqualsStringIgnoringLineEndings(
            <<<'EOT'
100 examples checked, 3 rejected

     52.0% even
     48.0% odd
      1.0% zero

EOT,
            PropertyCheckedToStringMapper::map(
                $this->event(
                    100,
                    3,
                    [
                        ['label' => 'zero', 'numberOfExamples' => 1],
                        ['label' => 'odd', 'numberOfExamples' => 48],
                        ['label' => 'even', 'numberOfExamples' => 52],
                    ],
                ),
            ),
        );
    }

    /**
     * @param non-negative-int                                                     $numberOfExamplesChecked
     * @param non-negative-int                                                     $numberOfExamplesRejected
     * @param list<array{label: non-empty-string, numberOfExamples: positive-int}> $classifications
     */
    private function event(int $numberOfExamplesChecked, int $numberOfExamplesRejected, array $classifications): PropertyChecked
    {
        return new PropertyChecked(
            $this->telemetryInfo(),
            $this->testValueObject(),
            1234,
            $numberOfExamplesChecked,
            $numberOfExamplesRejected,
            $classifications,
        );
    }
}
