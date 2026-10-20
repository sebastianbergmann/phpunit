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
final class PSR12Set extends AbstractRuleSetDefinition
{
    public function getRules(): array
    {
        return [
            '@PSR2' => true,
            'binary_operator_spaces' => [
                'default' => 'at_least_single_space',
            ],
            'blank_line_after_opening_tag' => true,
            'blank_line_between_import_groups' => true,
            'blank_lines_before_namespace' => true,
            'braces_position' => [
                'allow_single_line_anonymous_functions' => false,
                'allow_single_line_empty_anonymous_classes' => true,
            ],
            'class_definition' => [
                'inline_constructor_arguments' => false, // handled by method_argument_space fixer
                'space_before_parenthesis' => true, // defined in PSR12 ¶8. Anonymous Classes
            ],
            'compact_nullable_type_declaration' => true,
            'declare_equal_normalize' => true,
            'lowercase_cast' => true,
            'lowercase_static_reference' => true,
            'modifier_keywords' => true,
            'new_with_parentheses' => [
                'anonymous_class' => true,
            ],
            'no_blank_lines_after_class_opening' => true,
            'no_extra_blank_lines' => [
                'tokens' => [
                    'use', // defined in PSR12 ¶3. Declare Statements, Namespace, and Import Statements
                ],
            ],
            'no_leading_import_slash' => true,
            'no_whitespace_in_blank_line' => true,
            'ordered_class_elements' => [
                'order' => [
                    'use_trait',
                ],
            ],
            'ordered_imports' => [
                'imports_order' => [
                    'class',
                    'function',
                    'const',
                ],
                'sort_algorithm' => 'none',
            ],
            'return_type_declaration' => true,
            'short_scalar_cast' => true,
            'single_import_per_statement' => ['group_to_single_imports' => false],
            'single_space_around_construct' => [
                'constructs_followed_by_a_single_space' => [
                    'abstract',
                    'as',
                    'case',
                    'catch',
                    'class',
                    'const_import',
                    'do',
                    'else',
                    'elseif',
                    'final',
                    'finally',
                    'for',
                    'foreach',
                    'function',
                    'function_import',
                    'if',
                    'insteadof',
                    'interface',
                    'namespace',
                    'new',
                    'private',
                    'protected',
                    'public',
                    'static',
                    'switch',
                    'trait',
                    'try',
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
            'single_trait_insert_per_statement' => true,
            'ternary_operator_spaces' => true,
            'unary_operator_spaces' => [
                'only_dec_inc' => true,
            ],
        ];
    }

    public function getDescription(): string
    {
        return 'Rules that follow `PSR-12 <https://www.php-fig.org/psr/psr-12/>`_ standard.';
    }
}
