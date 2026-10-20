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
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PSR2Set extends AbstractRuleSetDefinition
{
    public function getRules(): array
    {
        return [
            '@PSR1' => true,
            'blank_line_after_namespace' => true,
            'braces_position' => [
                'allow_single_line_anonymous_functions' => false,
            ],
            'class_definition' => true,
            'constant_case' => true,
            'control_structure_braces' => true,
            'control_structure_continuation_position' => true,
            'elseif' => true,
            'function_declaration' => [
                'closure_fn_spacing' => 'one', // @TODO: default value of this option changed, consider to switch to new default
            ],
            'indentation_type' => true,
            'line_ending' => true,
            'lowercase_keywords' => true,
            'method_argument_space' => [
                'after_heredoc' => false, // @TODO: default value of this option changed, consider to switch to new default
                'attribute_placement' => 'ignore',
                'on_multiline' => 'ensure_fully_multiline',
            ],
            'modifier_keywords' => ['elements' => ['method', 'property']],
            'no_break_comment' => true,
            'no_closing_tag' => true,
            'no_multiple_statements_per_line' => true,
            'no_space_around_double_colon' => true,
            'no_spaces_after_function_name' => true,
            'no_trailing_whitespace' => true,
            'no_trailing_whitespace_in_comment' => true,
            'single_blank_line_at_eof' => true,
            'single_class_element_per_statement' => [
                'elements' => [
                    'property',
                ],
            ],
            'single_import_per_statement' => true,
            'single_line_after_imports' => true,
            'single_space_around_construct' => [
                'constructs_followed_by_a_single_space' => [
                    'abstract',
                    'as',
                    'case',
                    'catch',
                    'class',
                    'do',
                    'else',
                    'elseif',
                    'final',
                    'for',
                    'foreach',
                    'function',
                    'if',
                    'interface',
                    'namespace',
                    'private',
                    'protected',
                    'public',
                    'static',
                    'switch',
                    'trait',
                    'try',
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
            'spaces_inside_parentheses' => true,
            'statement_indentation' => true,
            'switch_case_semicolon_to_colon' => true,
            'switch_case_space' => true,
        ];
    }

    public function getDescription(): string
    {
        return 'Rules that follow `PSR-2 <https://www.php-fig.org/psr/psr-2/>`_ standard.';
    }
}
