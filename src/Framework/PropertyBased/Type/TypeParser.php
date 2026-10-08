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

use function assert;
use function count;
use function is_float;
use function is_string;
use function sprintf;
use PHPUnit\Framework\PropertyBased\InvalidTypeException;

/**
 * Parses the PHPDoc types that generators can be derived from:
 *
 *     type     := atom ('|' atom)*
 *     atom     := '?' atom | ( '(' type ')' | literal | name generic? shape? ) ('[' ']')*
 *     generic  := '<' type (',' type)* ','? '>'
 *     shape    := '{' (item (',' item)* (',' '...')? | '...')? ','? '}'
 *     item     := key '?'? ':' type | type
 *
 * A literal is an integer, floating-point, or string literal, and a key is an
 * identifier, an integer literal, or a string literal. Other syntax, for
 * instance intersection types, callable types, and conditional types, cannot
 * be parsed.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TypeParser
{
    /**
     * @throws InvalidTypeException
     */
    public function parse(string $type): Type
    {
        $lexer = new Lexer($type);

        $parsed = $this->parseFrom($lexer);

        $token = $lexer->peek();

        if ($token->kind() !== TokenKind::End) {
            throw $this->unexpected($token, 'end of the type');
        }

        return $parsed;
    }

    /**
     * Parses the type that the lexer is positioned at and leaves the lexer
     * positioned at the token that follows the type.
     *
     * @throws InvalidTypeException
     */
    public function parseFrom(Lexer $lexer): Type
    {
        $types = [$this->atom($lexer)];

        while ($lexer->peek()->isSymbol('|')) {
            $lexer->next();

            $types[] = $this->atom($lexer);
        }

        if (count($types) === 1) {
            return $types[0];
        }

        return new UnionType($types);
    }

    /**
     * @throws InvalidTypeException
     */
    private function atom(Lexer $lexer): Type
    {
        if ($lexer->peek()->isSymbol('?')) {
            $lexer->next();

            return new NullableType($this->atom($lexer));
        }

        $token = $lexer->next();

        if ($token->isSymbol('(')) {
            $type = $this->parseFrom($lexer);

            $this->expect($lexer, ')');

            return $this->arraySuffix($lexer, $type);
        }

        if ($token->kind() === TokenKind::Integer || $token->kind() === TokenKind::Float || $token->kind() === TokenKind::String) {
            return new LiteralType($token->value());
        }

        if ($token->kind() !== TokenKind::Identifier) {
            throw $this->unexpected($token, 'a type');
        }

        $name = $token->value();

        assert(is_string($name) && $name !== '');

        if ($lexer->peek()->isSymbol('<')) {
            return $this->arraySuffix($lexer, new NamedType($name, $this->genericArguments($lexer)));
        }

        if ($lexer->peek()->isSymbol('{')) {
            return $this->arraySuffix($lexer, $this->shape($lexer, $name));
        }

        return $this->arraySuffix($lexer, new NamedType($name));
    }

    /**
     * @throws InvalidTypeException
     */
    private function arraySuffix(Lexer $lexer, Type $type): Type
    {
        while ($lexer->peek()->isSymbol('[') && $lexer->peek(1)->isSymbol(']')) {
            $lexer->next();
            $lexer->next();

            $type = new NamedType('array', [$type]);
        }

        return $type;
    }

    /**
     * @throws InvalidTypeException
     *
     * @return non-empty-list<Type>
     */
    private function genericArguments(Lexer $lexer): array
    {
        $this->expect($lexer, '<');

        $arguments = [$this->parseFrom($lexer)];

        while ($lexer->peek()->isSymbol(',')) {
            $lexer->next();

            if ($lexer->peek()->isSymbol('>')) {
                break;
            }

            $arguments[] = $this->parseFrom($lexer);
        }

        $this->expect($lexer, '>');

        return $arguments;
    }

    /**
     * @param non-empty-string $name
     *
     * @throws InvalidTypeException
     */
    private function shape(Lexer $lexer, string $name): ArrayShapeType
    {
        $this->expect($lexer, '{');

        $items  = [];
        $sealed = true;

        while (!$lexer->peek()->isSymbol('}')) {
            if ($lexer->peek()->isSymbol('...')) {
                $lexer->next();

                $sealed = false;

                if ($lexer->peek()->isSymbol(',')) {
                    $lexer->next();
                }

                break;
            }

            $items[] = $this->shapeItem($lexer);

            if (!$lexer->peek()->isSymbol(',')) {
                break;
            }

            $lexer->next();
        }

        $this->expect($lexer, '}');

        return new ArrayShapeType($name, $items, $sealed);
    }

    /**
     * @throws InvalidTypeException
     */
    private function shapeItem(Lexer $lexer): ArrayShapeItem
    {
        if ($this->isKey($lexer)) {
            $key = $lexer->next()->value();

            assert(!is_float($key));

            $optional = false;

            if ($lexer->peek()->isSymbol('?')) {
                $lexer->next();

                $optional = true;
            }

            $this->expect($lexer, ':');

            return new ArrayShapeItem($key, $optional, $this->parseFrom($lexer));
        }

        return new ArrayShapeItem(null, false, $this->parseFrom($lexer));
    }

    /**
     * @throws InvalidTypeException
     */
    private function isKey(Lexer $lexer): bool
    {
        $kind = $lexer->peek()->kind();

        if ($kind !== TokenKind::Identifier && $kind !== TokenKind::Integer && $kind !== TokenKind::String) {
            return false;
        }

        if ($lexer->peek(1)->isSymbol(':')) {
            return true;
        }

        return $lexer->peek(1)->isSymbol('?') && $lexer->peek(2)->isSymbol(':');
    }

    /**
     * @throws InvalidTypeException
     */
    private function expect(Lexer $lexer, string $symbol): void
    {
        $token = $lexer->next();

        if (!$token->isSymbol($symbol)) {
            throw $this->unexpected($token, sprintf('"%s"', $symbol));
        }
    }

    private function unexpected(Token $token, string $expected): InvalidTypeException
    {
        return new InvalidTypeException(
            sprintf(
                'Unexpected %s, expected %s',
                $token->describe(),
                $expected,
            ),
        );
    }
}
