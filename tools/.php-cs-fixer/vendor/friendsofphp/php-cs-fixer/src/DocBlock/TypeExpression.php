<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\DocBlock;

use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;
use PhpCsFixer\Utils;

/**
 * @author Michael Vorisek <https://github.com/mvorisek>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class TypeExpression
{
    /**
     * Regex to match any PHP identifier.
     *
     * @internal
     */
    public const REGEX_IDENTIFIER = '(?:(?!(?<!\*)\d)[^\x00-\x2f\x3a-\x40\x5b-\x5e\x60\x7b-\x7f]++)';

    /**
     * Regex to match any PHPDoc type.
     *
     * @internal
     */
    public const REGEX_TYPES = '(?<types>(?x) # one or several types separated by `|` or `&`
'.self::REGEX_TYPE.'
        (?:
            \h*(?<glue>[|&])\h*
            (?&type)
        )*+
    )';

    /**
     * Based on:
     * - https://github.com/phpstan/phpdoc-parser/blob/1.26.0/doc/grammars/type.abnf fuzzing grammar
     * - and https://github.com/phpstan/phpdoc-parser/blob/1.26.0/src/Parser/PhpDocParser.php parser impl.
     */
    private const REGEX_TYPE = '(?<type>(?x) # single type
            (?:co(?:ntra)?variant\h+)?
            (?<nullable>\??\h*)
            (?:
                (?<array_shape>
                    (?<array_shape_name>(?i)(?:array|list|object)(?-i))
                    (?<array_shape_start>\h*\{[\h\v*]*)
                    (?<array_shape_inners>
                        (?<array_shape_inner>
                            (?<array_shape_inner_key>(?:(?&constant)|(?&identifier)|(?&name))\h*\??\h*:\h*|)
                            (?<array_shape_inner_value>(?&types_inner))
                        )
                        (?:
                            \h*,[\h\v*]*
                            (?&array_shape_inner)
                        )*+
                        (?:\h*,|(?!(?&array_shape_unsealed_variadic)))
                    |)
                    (?<array_shape_unsealed> # unsealed array shape, e.g. `...`. `...<string>`
                        (?<array_shape_unsealed_variadic>\h*\.\.\.)
                        (?<array_shape_unsealed_type>
                            (?<array_shape_unsealed_type_start>\h*<\h*)
                            (?<array_shape_unsealed_type_a>(?&types_inner))
                            (?:
                                (?<array_shape_unsealed_type_comma>\h*,\h*)
                                (?<array_shape_unsealed_type_b>(?&array_shape_unsealed_type_a))
                            |)
                            \h*>
                        |)
                    |)
                    [\h\v*]*\}
                )
                |
                (?<callable> # callable syntax, e.g. `callable(string, int...): bool`, `\Closure<T>(T, int): T`
                    (?<callable_name>(?&name))
                    (?<callable_template>
                        (?<callable_template_start>\h*<\h*)
                        (?<callable_template_inners>
                            (?<callable_template_inner>
                                (?<callable_template_inner_name>
                                    (?&identifier)
                                )
                                (?<callable_template_inner_b> # template bound
                                    \h+(?i)(?<callable_template_inner_b_kw>of|as)(?-i)\h+
                                    (?<callable_template_inner_b_types>(?&types_inner))
                                |)
                                (?<callable_template_inner_d> # template default
                                    \h*=\h*
                                    (?<callable_template_inner_d_types>(?&types_inner))
                                |)
                            )
                            (?:
                                \h*,\h*
                                (?&callable_template_inner)
                            )*+
                        )
                        \h*>
                        (?=\h*\()
                    |)
                    (?<callable_start>\h*\(\h*)
                    (?<callable_arguments>
                        (?<callable_argument>
                            (?<callable_argument_type>(?&types_inner))
                            (?<callable_argument_is_reference>\h*&|)
                            (?<callable_argument_is_variadic>\h*\.\.\.|)
                            (?<callable_argument_name>\h*\$(?&identifier)|)
                            (?<callable_argument_is_optional>\h*=|)
                        )
                        (?:
                            \h*,\h*
                            (?&callable_argument)
                        )*+
                        (?:\h*,)?
                    |)
                    \h*\)
                    (?:
                        \h*\:\h*
                        (?<callable_return>(?&type))
                    )?
                )
                |
                (?<generic> # generic syntax, e.g.: `array<int, \Foo\Bar>`
                    (?<generic_name>(?&name))
                    (?<generic_start>\h*<[\h\v*]*)
                    (?<generic_types>
                        (?&types_inner)
                        (?:
                            \h*,[\h\v*]*
                            (?&types_inner)
                        )*+
                        (?:\h*,)?
                    )
                    [\h\v*]*>
                )
                |
                (?<class_constant> # class constants with optional wildcard, e.g.: `Foo::*`, `Foo::CONST_A`, `FOO::CONST_*`
                    (?<class_constant_name>(?&name))
                    ::\*?(?:(?&identifier)\*?)*
                )
                |
                (?<constant> # single constant value (case insensitive), e.g.: 1, -1.8E+6, `\'a\'`
                    (?i)
                    # all sorts of numbers: with or without sign, supports literal separator and several numeric systems,
                    # e.g.: 1, +1.1, 1., .1, -1, 123E+8, 123_456_789, 0x7Fb4, 0b0110, 0o777
                    [+-]?(?:
                        (?:0b[01]++(?:_[01]++)*+)
                        | (?:0o[0-7]++(?:_[0-7]++)*+)
                        | (?:0x[\da-f]++(?:_[\da-f]++)*+)
                        | (?:(?<constant_digits>\d++(?:_\d++)*+)|(?=\.\d))
                          (?:\.(?&constant_digits)|(?<=\d)\.)?+
                          (?:e[+-]?(?&constant_digits))?+
                    )
                    | \'(?:[^\'\\\]|\\\.)*+\'
                    | "(?:[^"\\\]|\\\.)*+"
                    (?-i)
                )
                |
                (?<this> # self reference, e.g.: $this, $self, @static
                    (?i)
                    [@$](?:this | self | static)
                    (?-i)
                )
                |
                (?<name> # full name, e.g.: `int`, `\DateTime`, `\Foo\Bar`, `positive-int`
                    \\\?+
                    (?<identifier>'.self::REGEX_IDENTIFIER.')
                    (?:[\\\\\-](?&identifier))*+
                )
                |
                (?<parenthesized> # parenthesized type, e.g.: `(int)`, `(int|\stdClass)`
                    (?<parenthesized_start>
                        \(\h*
                    )
                    (?:
                        (?<parenthesized_types>
                            (?&types_inner)
                        )
                        |
                        (?<conditional> # conditional type, e.g.: `$foo is \Throwable ? false : $foo`
                            (?<conditional_cond_left>
                                (?:\$(?&identifier))
                                |
                                (?<conditional_cond_left_types>(?&types_inner))
                            )
                            (?<conditional_cond_middle>
                                \h+(?i)is(?:\h+not)?(?-i)\h+
                            )
                            (?<conditional_cond_right_types>(?&types_inner))
                            (?<conditional_true_start>\h*\?\h*)
                            (?<conditional_true_types>(?&types_inner))
                            (?<conditional_false_start>\h*:\h*)
                            (?<conditional_false_types>(?&types_inner))
                        )
                    )
                    \h*\)
                )
            )
            (?<array> # array, e.g.: `string[]`, `array<int, string>[][]`
                (\h*\[\h*\])*
            )
            (?:(?=1)0
                (?<types_inner>(?>
                    (?&type)
                    (?:
                        \h*[|&]\h*
                        (?&type)
                    )*+
                ))
            |)
        )';

    private const ALIASES = [
        'boolean' => 'bool',
        'callback' => 'callable',
        'double' => 'float',
        'false' => 'bool',
        'integer' => 'int',
        'list' => 'array',
        'real' => 'float',
        'true' => 'bool',
    ];

    private string $value;

    private bool $isCompositeType;

    /** @var null|'&'|'|' */
    private ?string $typesGlue = null;

    /** @var list<array{start_index: int, expression: self}> */
    private array $innerTypeExpressions = [];

    private ?NamespaceAnalysis $namespace;

    /** @var list<NamespaceUseAnalysis> */
    private array $namespaceUses;

    /**
     * @param list<NamespaceUseAnalysis> $namespaceUses
     */
    public function __construct(string $value, ?NamespaceAnalysis $namespace, array $namespaceUses)
    {
        $this->value = $value;
        $this->namespace = $namespace;
        $this->namespaceUses = $namespaceUses;

        $this->parse();
    }

    public function toString(): string
    {
        return $this->value;
    }

    /**
     * @return list<string>
     */
    public function getTypes(): array
    {
        if ($this->isCompositeType) {
            return array_map(
                static fn (array $type) => $type['expression']->toString(),
                $this->innerTypeExpressions,
            );
        }

        return [$this->value];
    }

    /**
     * Determines if type expression is a composite type (union or intersection).
     */
    public function isCompositeType(): bool
    {
        return $this->isCompositeType;
    }

    public function isUnionType(): bool
    {
        return $this->isCompositeType && '|' === $this->typesGlue;
    }

    public function isIntersectionType(): bool
    {
        return $this->isCompositeType && '&' === $this->typesGlue;
    }

    /**
     * @return null|'&'|'|'
     */
    public function getTypesGlue(): ?string
    {
        return $this->typesGlue;
    }

    /**
     * @param \Closure(self): self $callback
     */
    public function mapTypes(\Closure $callback): self
    {
        $value = $this->value;
        $startIndexOffset = 0;

        foreach ($this->innerTypeExpressions as [
            'start_index' => $startIndexOrig,
            'expression' => $inner,
        ]) {
            $innerValueOrig = $inner->value;

            $inner = $inner->mapTypes($callback);

            if ($inner->value !== $innerValueOrig) {
                $value = substr_replace(
                    $value,
                    $inner->value,
                    $startIndexOrig + $startIndexOffset,
                    \strlen($innerValueOrig),
                );

                $startIndexOffset += \strlen($inner->value) - \strlen($innerValueOrig);
            }
        }

        $type = $value === $this->value
            ? $this
            : $this->inner($value);

        return $callback($type);
    }

    /**
     * @param \Closure(self): void $callback
     */
    public function walkTypes(\Closure $callback): void
    {
        $this->mapTypes(static function (self $type) use ($callback) {
            $valueOrig = $type->value;
            $callback($type);
            \assert($type->value === $valueOrig);

            return $type;
        });
    }

    /**
     * @param \Closure(self, self): (-1|0|1) $compareCallback
     */
    public function sortTypes(\Closure $compareCallback): self
    {
        return $this->mapTypes(function (self $type) use ($compareCallback): self {
            if (!$type->isCompositeType) {
                return $type;
            }

            $innerTypeExpressions = Utils::stableSort(
                $type->innerTypeExpressions,
                static fn (array $v): self => $v['expression'],
                $compareCallback,
            );

            if ($innerTypeExpressions !== $type->innerTypeExpressions) {
                $value = implode(
                    $type->getTypesGlue(),
                    array_map(static fn (array $v): string => $v['expression']->toString(), $innerTypeExpressions),
                );

                return $this->inner($value);
            }

            return $type;
        });
    }

    public function removeDuplicateTypes(): self
    {
        return $this->mapTypes(function (self $type): self {
            if (!$type->isCompositeType) {
                return $type;
            }

            $seenNormalized = [];
            $uniqueTypeExpressions = [];

            foreach ($type->innerTypeExpressions as $innerType) {
                $normalized = $innerType['expression']
                    ->sortTypes(static fn (self $a, self $b): int => $a->toString() <=> $b->toString())
                    ->toString()
                ;

                if (!\in_array($normalized, $seenNormalized, true)) {
                    $seenNormalized[] = $normalized;
                    $uniqueTypeExpressions[] = $innerType['expression'];
                }
            }

            $value = implode(
                $type->getTypesGlue(),
                array_map(static fn (self $expr): string => $expr->toString(), $uniqueTypeExpressions),
            );

            return $this->inner($value);
        });
    }

    public function getCommonType(): ?string
    {
        $mainType = null;

        foreach ($this->getTypes() as $type) {
            if ('null' === $type) {
                continue;
            }

            if (str_starts_with($type, '?')) {
                $type = substr($type, 1);
            }

            if (Preg::match('/\[\h*\]$/', $type)) {
                $type = 'array';
            } elseif (Preg::match('/^(.+?)\h*[<{(]/', $type, $matches)) {
                $type = $matches[1];
            }

            if (isset(self::ALIASES[$type])) {
                $type = self::ALIASES[$type];
            }

            if (null === $mainType || $type === $mainType) {
                $mainType = $type;

                continue;
            }

            $mainType = $this->getParentType($type, $mainType);

            if (null === $mainType) {
                return null;
            }
        }

        return $mainType;
    }

    public function allowsNull(): bool
    {
        foreach ($this->getTypes() as $type) {
            if (\in_array($type, ['null', 'mixed'], true) || str_starts_with($type, '?')) {
                return true;
            }
        }

        return false;
    }

    private function parse(): void
    {
        $seenGlues = null;
        $innerValues = [];

        $index = 0;
        while (true) {
            Preg::match(
                '{\G'.self::REGEX_TYPE.'(?<glue_raw>\h*(?<glue>[|&])\h*(?!$)|$)}',
                $this->value,
                $matches,
                \PREG_OFFSET_CAPTURE,
                $index,
            );

            if ([] === $matches) {
                throw new \Exception('Unable to parse phpdoc type '.var_export($this->value, true));
            }

            \assert(isset($matches[0], $matches['type']));

            if (null === $seenGlues) {
                if (($matches['glue'][0] ?? '') === '') {
                    break;
                }

                $seenGlues = ['|' => false, '&' => false];
            }

            if (($matches['glue'][0] ?? '') !== '') {
                \assert(isset($seenGlues[$matches['glue'][0]]));
                $seenGlues[$matches['glue'][0]] = true;
            }

            $innerValues[] = [
                'start_index' => $index,
                'value' => $matches['type'][0],
                'next_glue' => $matches['glue'][0] ?? null,
                'next_glue_raw' => $matches['glue_raw'][0] ?? null,
            ];

            $consumedValueLength = \strlen($matches[0][0]);
            $index += $consumedValueLength;

            if (\strlen($this->value) <= $index) {
                \assert(\strlen($this->value) === $index);

                $seenGlues = array_filter($seenGlues);
                \assert([] !== $seenGlues);

                $this->isCompositeType = true;
                $this->typesGlue = array_key_first($seenGlues);

                if (1 === \count($seenGlues)) {
                    foreach ($innerValues as $innerValue) {
                        $this->innerTypeExpressions[] = [
                            'start_index' => $innerValue['start_index'],
                            'expression' => $this->inner($innerValue['value']),
                        ];
                    }
                } else {
                    for ($i = 0; $i < \count($innerValues); ++$i) {
                        $innerStartIndex = $innerValues[$i]['start_index'];
                        $innerValue = '';
                        while (true) {
                            $innerValue .= $innerValues[$i]['value'];

                            if (($innerValues[$i]['next_glue'] ?? $this->typesGlue) === $this->typesGlue) {
                                break;
                            }

                            $innerValue .= $innerValues[$i]['next_glue_raw'];

                            ++$i;
                            \assert(isset($innerValues[$i])); // for PHPStan
                        }

                        $this->innerTypeExpressions[] = [
                            'start_index' => $innerStartIndex,
                            'expression' => $this->inner($innerValue),
                        ];
                    }
                }

                return;
            }
        }

        $this->isCompositeType = false;

        \assert(isset($matches['nullable'], $matches['array'], $matches['class_constant']));

        if ('' !== $matches['nullable'][0]) {
            $this->innerTypeExpressions[] = [
                'start_index' => \strlen($matches['nullable'][0]),
                'expression' => $this->inner(substr($matches['type'][0], \strlen($matches['nullable'][0]))),
            ];
        } elseif ('' !== $matches['array'][0]) {
            $this->innerTypeExpressions[] = [
                'start_index' => 0,
                'expression' => $this->inner(substr($matches['type'][0], 0, -\strlen($matches['array'][0]))),
            ];
        } elseif ('' !== ($matches['generic'][0] ?? '') && 0 === $matches['generic'][1]) {
            \assert(isset($matches['generic_name'], $matches['generic_start'], $matches['generic_types']));

            $this->innerTypeExpressions[] = [
                'start_index' => 0,
                'expression' => $this->inner($matches['generic_name'][0]),
            ];

            $this->parseCommaSeparatedInnerTypes(
                \strlen($matches['generic_name'][0]) + \strlen($matches['generic_start'][0]),
                $matches['generic_types'][0],
            );
        } elseif ('' !== ($matches['callable'][0] ?? '') && 0 === $matches['callable'][1]) {
            \assert(isset($matches['callable_name'], $matches['callable_template'], $matches['callable_start'], $matches['callable_arguments'], $matches['callable_template_start'], $matches['callable_template_inners']));

            $this->innerTypeExpressions[] = [
                'start_index' => 0,
                'expression' => $this->inner($matches['callable_name'][0]),
            ];

            $this->parseCallableTemplateInnerTypes(
                \strlen($matches['callable_name'][0])
                    + \strlen($matches['callable_template_start'][0]),
                $matches['callable_template_inners'][0],
            );

            $this->parseCallableArgumentTypes(
                \strlen($matches['callable_name'][0])
                    + \strlen($matches['callable_template'][0])
                    + \strlen($matches['callable_start'][0]),
                $matches['callable_arguments'][0],
            );

            if ('' !== ($matches['callable_return'][0] ?? '')) {
                $this->innerTypeExpressions[] = [
                    'start_index' => \strlen($this->value) - \strlen($matches['callable_return'][0]),
                    'expression' => $this->inner($matches['callable_return'][0]),
                ];
            }
        } elseif ('' !== ($matches['array_shape'][0] ?? '') && 0 === $matches['array_shape'][1]) {
            \assert(isset($matches['array_shape_name'], $matches['array_shape_start'], $matches['array_shape_inners']));

            $this->innerTypeExpressions[] = [
                'start_index' => 0,
                'expression' => $this->inner($matches['array_shape_name'][0]),
            ];

            $nextIndex = \strlen($matches['array_shape_name'][0]) + \strlen($matches['array_shape_start'][0]);

            $this->parseArrayShapeInnerTypes(
                $nextIndex,
                $matches['array_shape_inners'][0],
            );

            if ('' !== ($matches['array_shape_unsealed_type'][0] ?? '')) {
                \assert(isset($matches['array_shape_unsealed_variadic'], $matches['array_shape_unsealed_type_start'], $matches['array_shape_unsealed_type_a']));

                $nextIndex += \strlen($matches['array_shape_inners'][0])
                    + \strlen($matches['array_shape_unsealed_variadic'][0])
                    + \strlen($matches['array_shape_unsealed_type_start'][0]);

                $this->innerTypeExpressions[] = [
                    'start_index' => $nextIndex,
                    'expression' => $this->inner($matches['array_shape_unsealed_type_a'][0]),
                ];

                if ('' !== ($matches['array_shape_unsealed_type_b'][0] ?? '')) {
                    \assert(isset($matches['array_shape_unsealed_type_comma']));

                    $nextIndex += \strlen($matches['array_shape_unsealed_type_a'][0])
                        + \strlen($matches['array_shape_unsealed_type_comma'][0]);

                    $this->innerTypeExpressions[] = [
                        'start_index' => $nextIndex,
                        'expression' => $this->inner($matches['array_shape_unsealed_type_b'][0]),
                    ];
                }
            }
        } elseif ('' !== ($matches['parenthesized'][0] ?? '') && 0 === $matches['parenthesized'][1]) {
            \assert(isset($matches['parenthesized_start']));
            $index = \strlen($matches['parenthesized_start'][0]);

            if ('' !== ($matches['conditional'][0] ?? '')) {
                \assert(isset($matches['conditional_cond_left'], $matches['conditional_cond_middle'], $matches['conditional_cond_right_types'], $matches['conditional_true_start'], $matches['conditional_true_types'], $matches['conditional_false_start'], $matches['conditional_false_types']));

                if ('' !== ($matches['conditional_cond_left_types'][0] ?? '')) {
                    $this->innerTypeExpressions[] = [
                        'start_index' => $index,
                        'expression' => $this->inner($matches['conditional_cond_left_types'][0]),
                    ];
                }

                $index += \strlen($matches['conditional_cond_left'][0]) + \strlen($matches['conditional_cond_middle'][0]);

                $this->innerTypeExpressions[] = [
                    'start_index' => $index,
                    'expression' => $this->inner($matches['conditional_cond_right_types'][0]),
                ];

                $index += \strlen($matches['conditional_cond_right_types'][0]) + \strlen($matches['conditional_true_start'][0]);

                $this->innerTypeExpressions[] = [
                    'start_index' => $index,
                    'expression' => $this->inner($matches['conditional_true_types'][0]),
                ];

                $index += \strlen($matches['conditional_true_types'][0]) + \strlen($matches['conditional_false_start'][0]);

                $this->innerTypeExpressions[] = [
                    'start_index' => $index,
                    'expression' => $this->inner($matches['conditional_false_types'][0]),
                ];
            } else {
                \assert(isset($matches['parenthesized_types']));

                $this->innerTypeExpressions[] = [
                    'start_index' => $index,
                    'expression' => $this->inner($matches['parenthesized_types'][0]),
                ];
            }
        } elseif ('' !== $matches['class_constant'][0]) {
            \assert(isset($matches['class_constant_name']));

            $this->innerTypeExpressions[] = [
                'start_index' => 0,
                'expression' => $this->inner($matches['class_constant_name'][0]),
            ];
        }
    }

    private function parseCommaSeparatedInnerTypes(int $startIndex, string $value): void
    {
        $index = 0;
        while (\strlen($value) !== $index) {
            Preg::match(
                '{\G'.self::REGEX_TYPES.'(?:\h*,[\h\v*]*|$)}',
                $value,
                $matches,
                0,
                $index,
            );

            \assert(isset($matches[0], $matches['types']));

            $this->innerTypeExpressions[] = [
                'start_index' => $startIndex + $index,
                'expression' => $this->inner($matches['types']),
            ];

            $index += \strlen($matches[0]);
        }
    }

    private function parseCallableTemplateInnerTypes(int $startIndex, string $value): void
    {
        $index = 0;
        while (\strlen($value) !== $index) {
            Preg::match(
                '{\G(?:(?=1)0'.self::REGEX_TYPES.'|(?<_callable_template_inner>(?&callable_template_inner))(?:\h*,\h*|$))}',
                $value,
                $prematches,
                0,
                $index,
            );
            \assert(isset($prematches[0], $prematches['_callable_template_inner']));
            $consumedValue = $prematches['_callable_template_inner'];
            $consumedValueLength = \strlen($consumedValue);
            $consumedCommaLength = \strlen($prematches[0]) - $consumedValueLength;

            $addedPrefix = 'Closure<';
            Preg::match(
                '{^'.self::REGEX_TYPES.'$}',
                $addedPrefix.$consumedValue.'>(): void',
                $matches,
                \PREG_OFFSET_CAPTURE,
            );

            \assert(isset($matches['callable_template_inner_b'], $matches['callable_template_inner_d']));

            if ('' !== $matches['callable_template_inner_b'][0]) {
                \assert(isset($matches['callable_template_inner_b_types']));

                $this->innerTypeExpressions[] = [
                    'start_index' => $startIndex + $index + $matches['callable_template_inner_b_types'][1]
                        - \strlen($addedPrefix),
                    'expression' => $this->inner($matches['callable_template_inner_b_types'][0]),
                ];
            }

            if ('' !== $matches['callable_template_inner_d'][0]) {
                \assert(isset($matches['callable_template_inner_d_types']));

                $this->innerTypeExpressions[] = [
                    'start_index' => $startIndex + $index + $matches['callable_template_inner_d_types'][1]
                        - \strlen($addedPrefix),
                    'expression' => $this->inner($matches['callable_template_inner_d_types'][0]),
                ];
            }

            $index += $consumedValueLength + $consumedCommaLength;
        }
    }

    private function parseCallableArgumentTypes(int $startIndex, string $value): void
    {
        $index = 0;
        while (\strlen($value) !== $index) {
            Preg::match(
                '{\G(?:(?=1)0'.self::REGEX_TYPES.'|(?<_callable_argument>(?&callable_argument))(?:\h*,\h*|$))}',
                $value,
                $prematches,
                0,
                $index,
            );
            \assert(isset($prematches[0], $prematches['_callable_argument']));
            $consumedValue = $prematches['_callable_argument'];
            $consumedValueLength = \strlen($consumedValue);
            $consumedCommaLength = \strlen($prematches[0]) - $consumedValueLength;

            $addedPrefix = 'Closure(';
            Preg::match(
                '{^'.self::REGEX_TYPES.'$}',
                $addedPrefix.$consumedValue.'): void',
                $matches,
                \PREG_OFFSET_CAPTURE,
            );

            \assert(isset($matches['callable_argument_type']));

            $this->innerTypeExpressions[] = [
                'start_index' => $startIndex + $index,
                'expression' => $this->inner($matches['callable_argument_type'][0]),
            ];

            $index += $consumedValueLength + $consumedCommaLength;
        }
    }

    private function parseArrayShapeInnerTypes(int $startIndex, string $value): void
    {
        $index = 0;
        while (\strlen($value) !== $index) {
            Preg::match(
                '{\G(?:(?=1)0'.self::REGEX_TYPES.'|(?<_array_shape_inner>(?&array_shape_inner))(?:\h*,[\h\v*]*|$))}',
                $value,
                $prematches,
                0,
                $index,
            );
            \assert(isset($prematches[0], $prematches['_array_shape_inner']));
            $consumedValue = $prematches['_array_shape_inner'];
            $consumedValueLength = \strlen($consumedValue);
            $consumedCommaLength = \strlen($prematches[0]) - $consumedValueLength;

            $addedPrefix = 'array{';
            Preg::match(
                '{^'.self::REGEX_TYPES.'$}',
                $addedPrefix.$consumedValue.'}',
                $matches,
                \PREG_OFFSET_CAPTURE,
            );

            \assert(isset($matches['array_shape_inner_value']));

            $this->innerTypeExpressions[] = [
                'start_index' => $startIndex + $index + $matches['array_shape_inner_value'][1]
                    - \strlen($addedPrefix),
                'expression' => $this->inner($matches['array_shape_inner_value'][0]),
            ];

            $index += $consumedValueLength + $consumedCommaLength;
        }
    }

    private function inner(string $value): self
    {
        return new self($value, $this->namespace, $this->namespaceUses);
    }

    private function getParentType(string $type1, string $type2): ?string
    {
        $types = [
            $this->normalize($type1),
            $this->normalize($type2),
        ];
        natcasesort($types);
        $types = implode('|', $types);

        $parents = [
            'array|Traversable' => 'iterable',
            'array|iterable' => 'iterable',
            'iterable|Traversable' => 'iterable',
            'self|static' => 'self',
        ];

        return $parents[$types] ?? null;
    }

    private function normalize(string $type): string
    {
        if (isset(self::ALIASES[$type])) {
            return self::ALIASES[$type];
        }

        if (\in_array($type, [
            'array',
            'bool',
            'callable',
            'false',
            'float',
            'int',
            'iterable',
            'mixed',
            'never',
            'null',
            'object',
            'resource',
            'string',
            'true',
            'void',
        ], true)) {
            return $type;
        }

        if (Preg::match('/\[\]$/', $type)) {
            return 'array';
        }

        if (Preg::match('/^(.+?)</', $type, $matches)) {
            return $matches[1];
        }

        if (str_starts_with($type, '\\')) {
            return substr($type, 1);
        }

        foreach ($this->namespaceUses as $namespaceUse) {
            if ($namespaceUse->getShortName() === $type) {
                return $namespaceUse->getFullName();
            }
        }

        if (null === $this->namespace || $this->namespace->isGlobalNamespace()) {
            return $type;
        }

        return "{$this->namespace->getFullName()}\\{$type}";
    }
}
