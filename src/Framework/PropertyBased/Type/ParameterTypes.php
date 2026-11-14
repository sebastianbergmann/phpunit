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
    private const array TAGS_IN_ORDER_OF_PRECEDENCE     = ['phpstan-param', 'psalm-param', 'param'];
    private const array VAR_TAGS_IN_ORDER_OF_PRECEDENCE = ['phpstan-var', 'psalm-var', 'var'];

    /**
     * @var array<non-empty-string, string|Type> the type, or why it cannot be parsed, by parameter name
     */
    private array $types;

    /**
     * @var array<non-empty-string, true> the parameters whose type is declared with a @var tag rather than with a @param tag
     */
    private array $declaredWithVarTag;

    public static function fromDocComment(false|string $docComment): self
    {
        if ($docComment === false) {
            return new self([], []);
        }

        $tags = [];

        foreach (self::TAGS_IN_ORDER_OF_PRECEDENCE as $tag) {
            $tags[$tag] = [];
        }

        $text = self::withoutDecoration($docComment);

        preg_match_all('/@(phpstan-param|psalm-param|param)(?=\s)/', $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        foreach ($matches as $match) {
            $parsed = self::parseTag(self::bodyOfTag($text, $match[0][1] + strlen($match[0][0])));

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

        return new self($types, []);
    }

    /**
     * @param array<non-empty-string, string|Type> $types
     * @param array<non-empty-string, true>        $declaredWithVarTag
     */
    private function __construct(array $types, array $declaredWithVarTag)
    {
        $this->types              = $types;
        $this->declaredWithVarTag = $declaredWithVarTag;
    }

    /**
     * Returns these types together with the types that the doc comments of
     * promoted constructor parameters declare with a @var tag, a @phpstan-var
     * tag, or a @psalm-var tag.
     *
     * The type in a @param tag of the constructor takes precedence over the
     * type in the @var tag of the parameter, as it does in PHPStan.
     *
     * @param array<non-empty-string, string> $docComments the doc comments of promoted constructor parameters by the names of the parameters
     */
    public function withTypesOfPromotedParameters(array $docComments): self
    {
        $types              = $this->types;
        $declaredWithVarTag = $this->declaredWithVarTag;

        foreach ($docComments as $name => $docComment) {
            if (isset($types[$name])) {
                continue;
            }

            $type = self::typeInVarTag($docComment);

            if ($type === null) {
                continue;
            }

            $types[$name]              = $type;
            $declaredWithVarTag[$name] = true;
        }

        return new self($types, $declaredWithVarTag);
    }

    public function has(string $parameterName): bool
    {
        return isset($this->types[$parameterName]);
    }

    /**
     * The tag that declares the type of the parameter: var for the @var tag,
     * the @phpstan-var tag, or the @psalm-var tag of a promoted constructor
     * parameter, param otherwise.
     *
     * @return 'param'|'var'
     */
    public function tagOf(string $parameterName): string
    {
        if (isset($this->declaredWithVarTag[$parameterName])) {
            return 'var';
        }

        return 'param';
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
     * The text of a tag from the given offset, which follows the name of the
     * tag, to the next tag or the end of the doc comment.
     */
    private static function bodyOfTag(string $text, int $offset): string
    {
        $body = substr($text, $offset);

        if (preg_match('/\R\h*@/', $body, $next, PREG_OFFSET_CAPTURE) === 1) {
            $body = substr($body, 0, $next[0][1]);
        }

        return $body;
    }

    /**
     * @return null|string|Type the type in the @var tag of the doc comment, or why it cannot be parsed, null when the doc comment has no @var tag with a type
     */
    private static function typeInVarTag(string $docComment): null|string|Type
    {
        $types = [];
        $text  = self::withoutDecoration($docComment);

        preg_match_all('/@(phpstan-var|psalm-var|var)(?=\s)/', $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        foreach ($matches as $match) {
            if (isset($types[$match[1][0]])) {
                continue;
            }

            $lexer = new Lexer(self::bodyOfTag($text, $match[0][1] + strlen($match[0][0])));

            try {
                // a tag without a type, such as @var $x, does not declare one
                if ($lexer->peek()->kind() === TokenKind::Variable || $lexer->peek()->kind() === TokenKind::End) {
                    continue;
                }

                $types[$match[1][0]] = (new TypeParser)->parseFrom($lexer);
            } catch (InvalidTypeException $e) {
                $types[$match[1][0]] = $e->getMessage();
            }
        }

        foreach (self::VAR_TAGS_IN_ORDER_OF_PRECEDENCE as $tag) {
            if (isset($types[$tag])) {
                return $types[$tag];
            }
        }

        return null;
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
