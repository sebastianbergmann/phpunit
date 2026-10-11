<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Type;

use const PHP_INT_MAX;
use const PHP_INT_MIN;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\InvalidTypeException;
use PHPUnit\Framework\TestCase;

#[CoversClass(TypeParser::class)]
#[CoversClass(Lexer::class)]
#[CoversClass(Token::class)]
#[CoversClass(NamedType::class)]
#[CoversClass(LiteralType::class)]
#[CoversClass(UnionType::class)]
#[CoversClass(NullableType::class)]
#[CoversClass(ArrayShapeType::class)]
#[CoversClass(ArrayShapeItem::class)]
#[CoversClass(InvalidTypeException::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class TypeParserTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function typeProvider(): array
    {
        return [
            'keyword'                                  => ['int', 'int'],
            'keyword with hyphens'                     => ['non-empty-string', 'non-empty-string'],
            'unqualified class name'                   => ['Money', 'Money'],
            'qualified class name'                     => ['App\Money', 'App\Money'],
            'fully qualified class name'               => ['\App\Money', '\App\Money'],
            'generic type'                             => ['list<int>', 'list<int>'],
            'generic type with two arguments'          => ['array<int, string>', 'array<int, string>'],
            'generic type with whitespace'             => ['array< int ,string >', 'array<int, string>'],
            'generic type with trailing comma'         => ['array<int, string,>', 'array<int, string>'],
            'integer range'                            => ['int<0, 150>', 'int<0, 150>'],
            'integer range with keywords'              => ['int<min, max>', 'int<min, max>'],
            'integer range with negative bound'        => ['int<-5, -1>', 'int<-5, -1>'],
            'integer range with extreme bounds'        => ['int<-9223372036854775808, 9223372036854775807>', 'int<-9223372036854775808, 9223372036854775807>'],
            'array suffix'                             => ['int[]', 'array<int>'],
            'repeated array suffix'                    => ['int[][]', 'array<array<int>>'],
            'array suffix of parenthesized type'       => ['(int|string)[]', 'array<int|string>'],
            'array suffix of generic type'             => ['list<int>[]', 'array<list<int>>'],
            'nullable type'                            => ['?int', '?int'],
            'nullable array'                           => ['?int[]', '?array<int>'],
            'nullable union'                           => ['?(int|string)', '?(int|string)'],
            'union'                                    => ['int|string|null', 'int|string|null'],
            'parenthesized type'                       => ['(int)', 'int'],
            'single-quoted string literals'            => ["'a'|'b'", "'a'|'b'"],
            'double-quoted string literal'             => ['"a"', "'a'"],
            'string literal with quote'                => ["'it\\'s'", "'it\\'s'"],
            'integer literal'                          => ['42', '42'],
            'negative integer literal'                 => ['-42', '-42'],
            'negative zero integer literal'            => ['-0', '0'],
            'integer literal with separators'          => ['1_000', '1000'],
            'hexadecimal integer literal'              => ['0x1F', '31'],
            'negative hexadecimal integer literal'     => ['-0x1F', '-31'],
            'binary integer literal'                   => ['0b101', '5'],
            'octal integer literal'                    => ['0o17', '15'],
            'octal integer literal with leading 0'     => ['017', '15'],
            'largest hexadecimal integer literal'      => ['0x7FFFFFFFFFFFFFFF', '9223372036854775807'],
            'float literal'                            => ['-1.5', '-1.5'],
            'float literal with exponent'              => ['1e3', '1000.0'],
            'float literal without integer part'       => ['.5', '0.5'],
            'array shape'                              => ['array{name: non-empty-string, age: int<0, 150>}', 'array{name: non-empty-string, age: int<0, 150>}'],
            'array shape with optional key'            => ['array{a: int, b?: string}', 'array{a: int, b?: string}'],
            'array shape with integer keys'            => ['array{0: int, 1?: string}', 'array{0: int, 1?: string}'],
            'array shape with quoted key'              => ["array{'first name': string}", "array{'first name': string}"],
            'array shape without keys'                 => ['list{int, string}', 'list{int, string}'],
            'unsealed array shape'                     => ['array{a: int, ...}', 'array{a: int, ...}'],
            'unsealed array shape without items'       => ['array{...}', 'array{...}'],
            'unsealed array shape with trailing comma' => ['array{a: int, ...,}', 'array{a: int, ...}'],
            'array shape with nullable item'           => ['list{?int, (int|string)}', 'list{?int, int|string}'],
            'empty array shape'                        => ['array{}', 'array{}'],
            'array shape with trailing comma'          => ['array{a: int,}', 'array{a: int}'],
            'array shape with keyword as key'          => ['array{int: string}', 'array{int: string}'],
            'array shape spanning lines'               => ["array{\n    a: int,\n    b: string\n}", 'array{a: int, b: string}'],
            'nested types'                             => ['list<array{id: positive-int, tags?: list<string>|null}>', 'list<array{id: positive-int, tags?: list<string>|null}>'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidTypeProvider(): array
    {
        return [
            'empty type'                    => ['', 'Unexpected end of the type, expected a type'],
            'unclosed generic type'         => ['list<int', 'Unexpected end of the type, expected ">"'],
            'unclosed array shape'          => ['array{a: int', 'Unexpected end of the type, expected "}"'],
            'unclosed parentheses'          => ['(int', 'Unexpected end of the type, expected ")"'],
            'text after the type'           => ['int>', 'Unexpected ">" at offset 3, expected end of the type'],
            'intersection type'             => ['A&B', 'Unexpected "&" at offset 1, expected end of the type'],
            'callable type'                 => ['callable(int): string', 'Unexpected "(" at offset 8, expected end of the type'],
            'class constant'                => ['Foo::BAR', 'Unexpected ":" at offset 3, expected end of the type'],
            'variable'                      => ['$x', 'Unexpected "$x" at offset 0, expected a type'],
            'missing type of shape item'    => ['array{a: }', 'Unexpected "}" at offset 9, expected a type'],
            'unterminated string literal'   => ["'unterminated", 'Unterminated string literal at offset 0'],
            'unexpected character'          => ['int#', 'Unexpected character "#" at offset 3'],
            'too large integer literal'     => ['9223372036854775808', 'Integer literal 9223372036854775808 at offset 0 is out of range'],
            'too small integer literal'     => ['-9223372036854775809', 'Integer literal -9223372036854775809 at offset 0 is out of range'],
            'too large hexadecimal literal' => ['0x8000000000000000', 'Integer literal 0x8000000000000000 at offset 0 is out of range'],
            'too large octal literal'       => ['01000000000000000000000', 'Integer literal 01000000000000000000000 at offset 0 is out of range'],
        ];
    }

    /**
     * @return array<string, array{string, float|int|string}>
     */
    public static function literalProvider(): array
    {
        return [
            'PHP_INT_MIN'                                => ['-9223372036854775808', PHP_INT_MIN],
            'PHP_INT_MAX'                                => ['9223372036854775807', PHP_INT_MAX],
            'float'                                      => ['1.5', 1.5],
            'single-quoted string with escape sequences' => ["'it\\'s a \\\\ and a \\n'", 'it\'s a \\ and a \\n'],
            'double-quoted string with escape sequences' => ['"a\\nb\\t\\"c\\" \\x41\\101 \\u{1F600} \\$ \\q"', "a\nb\t\"c\" AA \u{1F600} \$ \\q"],
        ];
    }

    #[DataProvider('typeProvider')]
    public function testParsesTypes(string $type, string $expected): void
    {
        $this->assertSame($expected, (new TypeParser)->parse($type)->asString());
    }

    #[DataProvider('invalidTypeProvider')]
    public function testReportsTypesThatCannotBeParsed(string $type, string $message): void
    {
        $this->expectException(InvalidTypeException::class);
        $this->expectExceptionMessage($message);

        (new TypeParser)->parse($type);
    }

    public function testParsesTheNameAndTheArgumentsOfANamedType(): void
    {
        $type = (new TypeParser)->parse('array<int, string>');

        $this->assertInstanceOf(NamedType::class, $type);
        $this->assertSame('array', $type->name());
        $this->assertCount(2, $type->arguments());
        $this->assertSame('int', $type->arguments()[0]->asString());
        $this->assertSame('string', $type->arguments()[1]->asString());
    }

    #[DataProvider('literalProvider')]
    public function testParsesTheValueOfALiteral(string $type, float|int|string $value): void
    {
        $type = (new TypeParser)->parse($type);

        $this->assertInstanceOf(LiteralType::class, $type);
        $this->assertSame($value, $type->value());
    }

    public function testParsesTheItemsOfAnArrayShape(): void
    {
        $type = (new TypeParser)->parse('array{a: int, 1?: string, ...}');

        $this->assertInstanceOf(ArrayShapeType::class, $type);
        $this->assertSame('array', $type->name());
        $this->assertFalse($type->isSealed());
        $this->assertCount(2, $type->items());

        $this->assertSame('a', $type->items()[0]->key());
        $this->assertFalse($type->items()[0]->isOptional());
        $this->assertSame('int', $type->items()[0]->type()->asString());

        $this->assertSame(1, $type->items()[1]->key());
        $this->assertTrue($type->items()[1]->isOptional());
        $this->assertSame('string', $type->items()[1]->type()->asString());
    }

    public function testParsesTheTypesOfAUnionAndOfANullableType(): void
    {
        $union = (new TypeParser)->parse('int|string');

        $this->assertInstanceOf(UnionType::class, $union);
        $this->assertCount(2, $union->types());

        $nullable = (new TypeParser)->parse('?int');

        $this->assertInstanceOf(NullableType::class, $nullable);
        $this->assertSame('int', $nullable->type()->asString());
    }

    public function testParsesATypeAtTheStartOfATextAndLeavesTheLexerPositionedAfterIt(): void
    {
        $lexer = new Lexer('list<int> $values The values');

        $this->assertSame('list<int>', (new TypeParser)->parseFrom($lexer)->asString());

        $token = $lexer->next();

        $this->assertSame(TokenKind::Variable, $token->kind());
        $this->assertSame('values', $token->value());
        $this->assertSame('$values', $token->text());
        $this->assertSame(10, $token->offset());
    }
}
