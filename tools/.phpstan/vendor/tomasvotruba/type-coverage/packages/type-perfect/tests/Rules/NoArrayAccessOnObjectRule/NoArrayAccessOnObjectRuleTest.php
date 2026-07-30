<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoArrayAccessOnObjectRule;

use Iterator;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\TypePerfect\Rules\NoArrayAccessOnObjectRule;

final class NoArrayAccessOnObjectRuleTest extends RuleTestCase
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
        yield [__DIR__ . '/Fixture/ArrayAccessOnObject.php', [[NoArrayAccessOnObjectRule::ERROR_MESSAGE, 14]]];
        yield [__DIR__ . '/Fixture/ArrayAccessOnNestedObject.php', [[NoArrayAccessOnObjectRule::ERROR_MESSAGE, 14]]];

        yield [__DIR__ . '/Fixture/SkipIterator.php', []];
        yield [__DIR__ . '/Fixture/SkipOnArray.php', []];
        yield [__DIR__ . '/Fixture/SkipSplFixedArray.php', []];
        yield [__DIR__ . '/Fixture/SkipUriElement.php', []];
        yield [__DIR__ . '/Fixture/SkipWeakMap.php', []];
        yield [__DIR__ . '/Fixture/SkipXml.php', []];
        yield [__DIR__ . '/Fixture/SkipXmlElementForeach.php', []];
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
        return self::getContainer()->getByType(NoArrayAccessOnObjectRule::class);
    }
}
