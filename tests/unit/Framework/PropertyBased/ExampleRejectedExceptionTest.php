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

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(ExampleRejectedException::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class ExampleRejectedExceptionTest extends TestCase
{
    /**
     * @return array<string, array{Closure(): ExampleRejectedException, RejectionReason, non-empty-string}>
     */
    public static function exceptionProvider(): array
    {
        return [
            'filter not satisfied' => [
                static fn (): ExampleRejectedException => ExampleRejectedException::filterNotSatisfied(3),
                RejectionReason::FilterNotSatisfied,
                'No value that satisfies the filter was generated in 3 attempts',
            ],
            'too many choices' => [
                static fn (): ExampleRejectedException => ExampleRejectedException::tooManyChoices(8192),
                RejectionReason::TooManyChoices,
                'Generating the example required more than 8192 choices',
            ],
            'objects nested too deeply' => [
                static fn (): ExampleRejectedException => ExampleRejectedException::objectsNestedTooDeeply(stdClass::class, 5),
                RejectionReason::ObjectsNestedTooDeeply,
                'Generating the example required more than 5 nested objects of class stdClass',
            ],
            'too many duplicate elements' => [
                static fn (): ExampleRejectedException => ExampleRejectedException::tooManyDuplicateElements(10),
                RejectionReason::TooManyDuplicateElements,
                'No new element for the collection was generated in 10 attempts',
            ],
            'no character of class' => [
                static fn (): ExampleRejectedException => ExampleRejectedException::noCharacterOfClass(64),
                RejectionReason::NoCharacterOfClass,
                'None of 64 characters that were tried is matched by the character class',
            ],
            'does not match regular expression' => [
                static fn (): ExampleRejectedException => ExampleRejectedException::doesNotMatchRegularExpression('/^a$/'),
                RejectionReason::DoesNotMatchRegularExpression,
                'The generated string is not matched by the regular expression /^a$/',
            ],
        ];
    }

    /**
     * @param Closure(): ExampleRejectedException $exception
     */
    #[DataProvider('exceptionProvider')]
    public function testKnowsWhyTheExampleWasRejected(Closure $exception, RejectionReason $reason, string $message): void
    {
        $this->assertSame($reason, $exception()->reason());
    }

    /**
     * @param Closure(): ExampleRejectedException $exception
     */
    #[DataProvider('exceptionProvider')]
    public function testDescribesWhyTheExampleWasRejected(Closure $exception, RejectionReason $reason, string $message): void
    {
        $this->assertSame($message, $exception()->getMessage());
    }
}
