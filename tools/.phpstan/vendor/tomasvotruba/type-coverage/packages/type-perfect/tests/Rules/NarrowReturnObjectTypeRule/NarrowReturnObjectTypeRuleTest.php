<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule;

use Iterator;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\TypePerfect\Rules\NarrowReturnObjectTypeRule;
use Rector\TypePerfect\Tests\Rules\NarrowReturnObjectTypeRule\Source\SpecificControl;

final class NarrowReturnObjectTypeRuleTest extends RuleTestCase
{
    /**
     * @param list<array{0: string, 1: int, 2?: string|null}> $expectedErrorMessagesWithLines
     */
    #[DataProvider('provideData')]
    public function testRule(string $filePath, array $expectedErrorMessagesWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorMessagesWithLines);
    }

    /**
     * @return Iterator<array<array<int, mixed>, mixed>>
     */
    public static function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/SkipSpecificReturnType.php', []];
        yield [__DIR__ . '/Fixture/SkipSomeContract.php', []];

        $errorMessage = sprintf(NarrowReturnObjectTypeRule::ERROR_MESSAGE, SpecificControl::class);
        yield [__DIR__ . '/Fixture/SomeAbstractReturnType.php', [[$errorMessage, 12]]];
    }

    /**
     * @return string[]
     */
    #[Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../config/extension.neon'];
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(NarrowReturnObjectTypeRule::class);
    }
}
