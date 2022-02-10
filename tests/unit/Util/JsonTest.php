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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(Json::class)]
#[Small]
final class JsonTest extends TestCase
{
    #[DataProvider('canonicalizeProvider')]
    #[TestDox('Canonicalize $actual')]
    public function testCanonicalize(string $actual, string $expected, bool $expectError): void
    {
        [$error, $canonicalized] = Json::canonicalize($actual);

        $this->assertEquals($expectError, $error);

        if (!$expectError) {
            $this->assertEquals($expected, $canonicalized);
        }
    }

    public function canonicalizeProvider(): array
    {
        return [
            ['{"name":"John","age":"35"}', '{"age":"35","name":"John"}', false],
            ['{"name":"John","age":"35","kids":[{"name":"Petr","age":"5"}]}', '{"age":"35","kids":[{"age":"5","name":"Petr"}],"name":"John"}', false],
            ['"name":"John","age":"35"}', '{"age":"35","name":"John"}', true],
        ];
    }

    #[DataProvider('prettifyProvider')]
    #[TestDox('Prettify $actual to $expected')]
    public function testPrettify(string $actual, string $expected): void
    {
        $this->assertEquals($expected, Json::prettify($actual));
    }

    public function prettifyProvider(): array
    {
        return [
            ['{"name":"John","age": "5"}', "{\n    \"name\": \"John\",\n    \"age\": \"5\"\n}"],
            ['{"url":"https://www.example.com/"}', "{\n    \"url\": \"https://www.example.com/\"\n}"],
            ['"Кириллица and 中文"', '"Кириллица and 中文"'],
        ];
    }

    #[DataProvider('prettifyExceptionProvider')]
    public function testPrettifyException(string $json): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot prettify invalid json');

        Json::prettify($json);
    }

    public function prettifyExceptionProvider(): array
    {
        return [
            ['"name":"John","age": "5"}'],
            [''],
        ];
    }
}
