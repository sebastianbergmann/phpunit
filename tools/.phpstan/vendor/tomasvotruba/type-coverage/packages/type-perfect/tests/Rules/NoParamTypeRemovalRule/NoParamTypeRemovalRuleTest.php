<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoParamTypeRemovalRule;

use Iterator;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\TypePerfect\Rules\NoParamTypeRemovalRule;

final class NoParamTypeRemovalRuleTest extends RuleTestCase
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
        yield [__DIR__ . '/Fixture/SkipConstruct.php', []];
        yield [__DIR__ . '/Fixture/SkipPhpDocType.php', []];
        yield [__DIR__ . '/Fixture/SkipPresentType.php', []];
        yield [__DIR__ . '/Fixture/SkipNoType.php', []];

        yield [__DIR__ . '/Fixture/SkipIndirectRemoval.php', []];

        yield [__DIR__ . '/Fixture/RemoveParentType.php', [[NoParamTypeRemovalRule::ERROR_MESSAGE, 11]]];

        yield [__DIR__ . '/Fixture/SkipNoParent.php', []];
        yield [__DIR__ . '/Fixture/SkipNotHasParentMethod.php', []];
        yield [__DIR__ . '/Fixture/SkipHasSameParameterWithParentMethod.php', []];
        yield [__DIR__ . '/Fixture/SkipHasSameParameterWithInterfaceMethod.php', []];

        yield [
            __DIR__ . '/Fixture/HasDifferentParameterWithParentMethod.php',
            [[NoParamTypeRemovalRule::ERROR_MESSAGE, 9]],
        ];
        yield [
            __DIR__ . '/Fixture/HasDifferentParameterWithInterfaceMethod.php',
            [[NoParamTypeRemovalRule::ERROR_MESSAGE, 9], [NoParamTypeRemovalRule::ERROR_MESSAGE, 13]],
        ];
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
        return self::getContainer()->getByType(NoParamTypeRemovalRule::class);
    }
}
