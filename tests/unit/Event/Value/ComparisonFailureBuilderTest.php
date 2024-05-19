<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\Comparator\ComparisonFailure;
use stdClass;

#[CoversClass(ComparisonFailureBuilder::class)]
#[Small]
final class ComparisonFailureBuilderTest extends TestCase
{
    public static function provider(): array
    {
        return [
            'type of expected value: string, string representation of expected value: not available, type of actual value: string, string representation of actual value: not available' => [
                'expected',
                'actual',
                '',
                new ExpectationFailedException(
                    'message',
                    new ComparisonFailure(
                        'expected',
                        'actual',
                        '',
                        '',
                        'message',
                    ),
                ),
            ],

            'type of expected value: true, string representation of expected value: not available, type of actual value: false, string representation of actual value: not available' => [
                'true',
                'false',
                '',
                new ExpectationFailedException(
                    'message',
                    new ComparisonFailure(
                        true,
                        false,
                        '',
                        '',
                        'message',
                    ),
                ),
            ],

            'type of expected value: null, string representation of expected value: not available, type of actual value: null, string representation of actual value: not available' => [
                'null',
                'null',
                '',
                new ExpectationFailedException(
                    'message',
                    new ComparisonFailure(
                        null,
                        null,
                        '',
                        '',
                        'message',
                    ),
                ),
            ],

            'type of expected value: array, string representation of expected value: not available, type of actual value: object, string representation of actual value: not available' => [
                '',
                '',
                '',
                new ExpectationFailedException(
                    'message',
                    new ComparisonFailure(
                        [],
                        new stdClass,
                        '',
                        '',
                        'message',
                    ),
                ),
            ],

            'type of expected value: string, string representation of expected value: available, type of actual value: string, string representation of actual value: available' => [
                'expected-string',
                'actual-string',
                <<<'EOT'

--- Expected
+++ Actual
@@ @@
-expected-string
+actual-string

EOT,
                new ExpectationFailedException(
                    'message',
                    new ComparisonFailure(
                        'expected',
                        'actual',
                        'expected-string',
                        'actual-string',
                        'message',
                    ),
                ),
            ],
        ];
    }

    #[TestDox('Maps exception that is not of type ExpectationFailedException to null')]
    public function testMapsGenericThrowableToNull(): void
    {
        $this->assertNull(
            ComparisonFailureBuilder::from(new Exception),
        );
    }

    #[TestDox('Maps ExpectationFailedException that does not aggregate a ComparisonFailure object to null')]
    public function testMapsExpectationFailedExceptionWithoutComparisonFailureToNull(): void
    {
        $this->assertNull(
            ComparisonFailureBuilder::from(
                new ExpectationFailedException('message'),
            ),
        );
    }

    #[DataProvider('provider')]
    #[TestDox('Maps ExpectationFailedException that aggregates a ComparisonFailure object to value object')]
    public function testMapsExpectationFailedExceptionWithComparisonFailureToValueObject(string $expected, string $actual, string $diff, ExpectationFailedException $exception): void
    {
        $comparisonFailure = ComparisonFailureBuilder::from($exception);

        $this->assertSame($expected, $comparisonFailure->expected());
        $this->assertSame($actual, $comparisonFailure->actual());
        $this->assertSame($diff, $comparisonFailure->diff());
    }
}
