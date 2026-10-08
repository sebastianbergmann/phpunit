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

use const PREG_OFFSET_CAPTURE;
use const PREG_SET_ORDER;
use function assert;
use function is_string;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function sprintf;
use function strlen;
use function substr;
use PHPUnit\Framework\PropertyBased\InvalidTypeException;

/**
 * The types of the parameters of a method as they are declared in its doc
 * comment with the tags param, phpstan-param, and psalm-param.
 *
 * When there is more than one tag for a parameter, phpstan-param takes
 * precedence over psalm-param, which takes precedence over param. A type
 * that cannot be parsed is only reported when the type of its parameter is
 * requested.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ParameterTypes
{
    private const array TAGS_IN_ORDER_OF_PRECEDENCE = ['phpstan-param', 'psalm-param', 'param'];

    /**
     * @var array<non-empty-string, string|Type> the type, or why it cannot be parsed, by parameter name
     */
    private array $types;

    public static function fromDocComment(false|string $docComment): self
    {
        if ($docComment === false) {
            return new self([]);
        }

        $tags = [];

        foreach (self::TAGS_IN_ORDER_OF_PRECEDENCE as $tag) {
            $tags[$tag] = [];
        }

        $text = self::withoutDecoration($docComment);

        preg_match_all('/@(phpstan-param|psalm-param|param)(?=\s)/', $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        foreach ($matches as $match) {
            $start = $match[0][1] + strlen($match[0][0]);
            $body  = substr($text, $start);

            if (preg_match('/\R\h*@/', $body, $next, PREG_OFFSET_CAPTURE) === 1) {
                $body = substr($body, 0, $next[0][1]);
            }

            $parsed = self::parseTag($body);

            if ($parsed === null) {
                continue;
            }

            [$name, $type] = $parsed;

            if (!isset($tags[$match[1][0]][$name])) {
                $tags[$match[1][0]][$name] = $type;
            }
        }

        $types = [];

        foreach (self::TAGS_IN_ORDER_OF_PRECEDENCE as $tag) {
            foreach ($tags[$tag] as $name => $type) {
                if (!isset($types[$name])) {
                    $types[$name] = $type;
                }
            }
        }

        return new self($types);
    }

    /**
     * @param array<non-empty-string, string|Type> $types
     */
    private function __construct(array $types)
    {
        $this->types = $types;
    }

    public function has(string $parameterName): bool
    {
        return isset($this->types[$parameterName]);
    }

    /**
     * @throws InvalidTypeException when the type of the parameter cannot be parsed
     */
    public function typeOf(string $parameterName): ?Type
    {
        if (!isset($this->types[$parameterName])) {
            return null;
        }

        $type = $this->types[$parameterName];

        if (is_string($type)) {
            throw new InvalidTypeException($type);
        }

        return $type;
    }

    /**
     * Removes the opening and closing delimiters of the doc comment and the
     * asterisks at the start of its lines.
     */
    private static function withoutDecoration(string $docComment): string
    {
        $text = preg_replace(['#^\s*/\*\*#', '#\*/\s*$#', '#^\h*\*(?!/)\h?#m'], '', $docComment);

        assert(is_string($text));

        return $text;
    }

    /**
     * @return ?array{non-empty-string, string|Type} the name of the parameter and its type, or why its type cannot be parsed
     */
    private static function parseTag(string $body): ?array
    {
        $lexer = new Lexer($body);

        try {
            // a tag without a type, such as @param $x, does not declare one
            if ($lexer->peek()->kind() === TokenKind::Variable) {
                return null;
            }

            $type = (new TypeParser)->parseFrom($lexer);

            if ($lexer->peek()->isSymbol('&')) {
                $lexer->next();
            }

            if ($lexer->peek()->isSymbol('...')) {
                $lexer->next();
            }

            $token = $lexer->next();

            if ($token->kind() !== TokenKind::Variable) {
                throw new InvalidTypeException(
                    sprintf(
                        'Unexpected %s, expected the name of the parameter',
                        $token->describe(),
                    ),
                );
            }

            $name = $token->value();

            assert(is_string($name) && $name !== '');

            return [$name, $type];
        } catch (InvalidTypeException $e) {
            // the type cannot be parsed, so the parameter it belongs to is
            // assumed to be the first variable in the tag
            if (preg_match('/\$([A-Za-z_\x80-\xFF][A-Za-z0-9_\x80-\xFF]*)/', $body, $matches) !== 1) {
                return null;
            }

            return [$matches[1], $e->getMessage()];
        }
    }
}
