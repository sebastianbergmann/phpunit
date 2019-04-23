<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

final class FunctionsTest extends TestCase
{
    private static $globalAssertionFunctions = [];

    public static function setUpBeforeClass(): void
    {
        \preg_match_all(
            '/function (assert[^ \(]+)/',
            \file_get_contents(
                __DIR__ . '/../../../../src/Framework/Assert/Functions.php'
            ),
            $matches
        );

        self::$globalAssertionFunctions = $matches[1];
    }

    /**
     * @dataProvider provideStaticAssertionMethodNames
     */
    public function testGlobalFunctionsFileContainsAllStaticAssertions(string $methodName): void
    {
        Assert::assertContains(
            $methodName,
            self::$globalAssertionFunctions,
            "Mapping for Assert::$methodName is missing in Functions.php"
        );
    }

    public function provideStaticAssertionMethodNames(): array
    {
        \preg_match_all(
            '/public static function (assert[^ \(]+)/',
            \file_get_contents(
                __DIR__ . '/../../../../src/Framework/Assert.php'
            ),
            $matches
        );

        return \array_reduce(
            $matches[1],
            function (array $functionNames, string $functionName) {
                $functionNames[$functionName] = [$functionName];

                return $functionNames;
            },
            []
        );
    }
}
