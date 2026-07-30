<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Tests\Rules\NarrowPrivateClassMethodParamTypeRule;

use Iterator;
use Override;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Param;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\TypePerfect\Rules\NarrowPrivateClassMethodParamTypeRule;

final class NarrowPrivateClassMethodParamTypeRuleTest extends RuleTestCase
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
        yield [__DIR__ . '/Fixture/SkipDuplicatedCallOfSameMethodWithComment.php', []];

        yield [__DIR__ . '/Fixture/SkipCorrectUnionType.php', []];
        yield [__DIR__ . '/Fixture/SkipRecursive.php', []];
        yield [__DIR__ . '/Fixture/SkipMixed.php', []];
        yield [__DIR__ . '/Fixture/SkipOptedOut.php', []];
        yield [__DIR__ . '/Fixture/SkipNotFromThis.php', []];

        yield [__DIR__ . '/Fixture/SkipParentNotIf.php', []];
        yield [__DIR__ . '/Fixture/SkipNoArgs.php', []];
        yield [__DIR__ . '/Fixture/SkipAlreadyCorrectType.php', []];
        yield [__DIR__ . '/Fixture/SkipMayOverrideArg.php', []];
        yield [__DIR__ . '/Fixture/SkipMultipleUsed.php', []];
        yield [__DIR__ . '/Fixture/SkipNotPrivate.php', []];

        $errorMessage = sprintf(NarrowPrivateClassMethodParamTypeRule::ERROR_MESSAGE, 1, MethodCall::class);
        yield [__DIR__ . '/Fixture/Fixture.php', [[$errorMessage, 19]]];
        yield [__DIR__ . '/Fixture/DifferentClassSameMethodCallName.php', [[$errorMessage, 25]]];

        $argErrorMessage = sprintf(NarrowPrivateClassMethodParamTypeRule::ERROR_MESSAGE, 1, Arg::class);
        $paramErrorMessage = sprintf(NarrowPrivateClassMethodParamTypeRule::ERROR_MESSAGE, 2, Param::class);
        yield [__DIR__ . '/Fixture/DoubleShot.php', [[$argErrorMessage, 15], [$paramErrorMessage, 15]]];
        yield [__DIR__ . '/Fixture/SkipGenericType.php', []];
        yield [__DIR__ . '/Fixture/SkipAbstractBase.php', []];
        yield [__DIR__ . '/Fixture/SkipVariadics.php', []];
        yield [__DIR__ . '/Fixture/SkipSameGeneric.php', []];
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
        return self::getContainer()->getByType(NarrowPrivateClassMethodParamTypeRule::class);
    }
}
