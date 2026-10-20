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

namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractRuleSetDefinition;

/**
 * @internal
 *
 * PER Coding Style v2.0.
 *
 * @see https://github.com/php-fig/per-coding-style/blob/2.0.0/spec.md
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PERCS2x0Set extends AbstractRuleSetDefinition
{
    public function getName(): string
    {
        return '@PER-CS2x0';
    }

    public function getRules(): array
    {
        return [
            '@PER-CS1x0' => true,
            'array_indentation' => true,
            'array_syntax' => true,
            'attribute_block_no_spaces' => true,
            'cast_spaces' => true,
            'concat_space' => ['spacing' => 'one'],
            'function_declaration' => [
                'closure_fn_spacing' => 'none',
            ],
            'method_argument_space' => [
                'after_heredoc' => false, // @TODO: default value of this option changed, consider to switch to new default
            ],
            'new_with_parentheses' => [
                'anonymous_class' => false,
            ],
            'single_line_empty_body' => true,
            'single_space_around_construct' => [
                'constructs_followed_by_a_single_space' => [
                    'abstract',
                    'as',
                    'case',
                    'catch',
                    'class',
                    'const',
                    'const_import',
                    'do',
                    'else',
                    'elseif',
                    'enum',
                    'final',
                    'finally',
                    'for',
                    'foreach',
                    'function',
                    'function_import',
                    'if',
                    'insteadof',
                    'interface',
                    'match',
                    'named_argument',
                    'namespace',
                    'new',
                    'private',
                    'protected',
                    'public',
                    'readonly',
                    'static',
                    'switch',
                    'trait',
                    'try',
                    'type_colon',
                    'use',
                    'use_lambda',
                    'while',
                ],
                'constructs_preceded_by_a_single_space' => [
                    'as',
                    'else',
                    'elseif',
                    'use_lambda',
                ],
            ],
            'trailing_comma_in_multiline' => [
                'after_heredoc' => true,
                'elements' => [
                    'arguments',
                    'array_destructuring',
                    'arrays',
                    'match',
                    'parameters',
                ],
            ],
        ];
    }

    public function getDescription(): string
    {
        return 'Rules that follow `PER Coding Style 2.0 <https://www.php-fig.org/per/coding-style/>`_.';
    }
}
