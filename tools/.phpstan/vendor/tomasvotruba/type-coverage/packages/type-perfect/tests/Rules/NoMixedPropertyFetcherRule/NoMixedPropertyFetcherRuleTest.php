<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoMixedPropertyFetcherRule;

use Iterator;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\TypePerfect\Rules\NoMixedPropertyFetcherRule;

final class NoMixedPropertyFetcherRuleTest extends RuleTestCase
{
    /**
     * @param list<array{0: string, 1: int, 2?: string|null}> $expectedErrorsWithLines
     */
    #[DataProvider('provideData')]
    public function testRule(string $filePath, array $expectedErrorsWithLines): void
    {
        $this->analyse([$filePath], $expectedErrorsWithLines);
    }

    /**
     * @return Iterator<array<array<int, mixed>, mixed>>
     */
    public static function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/SkipDynamicNameWithKnownType.php', []];
        yield [__DIR__ . '/Fixture/SkipKnownFetcherType.php', []];

        $message = sprintf(NoMixedPropertyFetcherRule::ERROR_MESSAGE, '$unknownType');
        yield [__DIR__ . '/Fixture/DynamicName.php', [[$message, 11]]];

        $message = sprintf(NoMixedPropertyFetcherRule::ERROR_MESSAGE, '$unknownType');
        yield [__DIR__ . '/Fixture/UnknownPropertyFetcher.php', [[$message, 11]]];
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
        return self::getContainer()->getByType(NoMixedPropertyFetcherRule::class);
    }
}
