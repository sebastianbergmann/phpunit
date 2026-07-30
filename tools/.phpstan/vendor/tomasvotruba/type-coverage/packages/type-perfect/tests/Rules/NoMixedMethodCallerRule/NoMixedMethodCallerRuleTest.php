<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NoMixedMethodCallerRule;

use Iterator;
use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\TypePerfect\Rules\NoMixedMethodCallerRule;

final class NoMixedMethodCallerRuleTest extends RuleTestCase
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
        yield [__DIR__ . '/Fixture/SkipKnownCallerType.php', []];
        yield [__DIR__ . '/Fixture/SkipMockObject.php', []];
        yield [__DIR__ . '/Fixture/SkipPHPUnitMock.php', []];

        $errorMessage = sprintf(NoMixedMethodCallerRule::ERROR_MESSAGE, '$someType');
        yield [__DIR__ . '/Fixture/MagicMethodName.php', [[$errorMessage, 11]]];

        $errorMessage = sprintf(NoMixedMethodCallerRule::ERROR_MESSAGE, '$mixedType');
        yield [__DIR__ . '/Fixture/UnknownCallerType.php', [[$errorMessage, 11]]];
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
        return self::getContainer()->getByType(NoMixedMethodCallerRule::class);
    }
}
