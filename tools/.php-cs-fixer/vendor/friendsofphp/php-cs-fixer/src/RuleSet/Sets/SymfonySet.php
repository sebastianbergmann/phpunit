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

use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use PhpCsFixer\RuleSet\AbstractRuleSetDefinition;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class SymfonySet extends AbstractRuleSetDefinition
{
    public function getRules(): array
    {
        return [
            '@PER-CS3x0' => true,
            'align_multiline_comment' => true,
            'backtick_to_shell_exec' => true,
            'binary_operator_spaces' => true,
            'blank_line_before_statement' => [
                'statements' => [
                    'return',
                ],
            ],
            'braces_position' => [
                'allow_single_line_anonymous_functions' => true,
                'allow_single_line_empty_anonymous_classes' => true,
            ],
            'class_attributes_separation' => [
                'elements' => [
                    'method' => 'one',
                ],
            ],
            'class_definition' => [
                'single_line' => true,
            ],
            'class_reference_name_casing' => true,
            'clean_namespace' => true,
            'concat_space' => true, // overrides @PER-CS2.0
            'declare_parentheses' => true,
            'echo_tag_syntax' => true,
            'empty_loop_body' => ['style' => 'braces'],
            'empty_loop_condition' => true,
            'fully_qualified_strict_types' => true,
            'function_declaration' => [ // overrides @PER-CS2.0
                'closure_fn_spacing' => 'one', // @TODO: default value of this option changed, consider to switch to new default
            ],
            'general_phpdoc_tag_rename' => [
                'replacements' => [
                    'inheritDocs' => 'inheritDoc',
                ],
            ],
            'global_namespace_import' => [
                'import_classes' => false,
                'import_constants' => false,
                'import_functions' => false,
            ],
            'include' => true,
            'increment_style' => true,
            'integer_literal_case' => true,
            'lambda_not_used_import' => true,
            'linebreak_after_opening_tag' => true,
            'magic_constant_casing' => true,
            'magic_method_casing' => true,
            'method_argument_space' => [ // overrides @PER-CS2.0
                'after_heredoc' => true,
                'on_multiline' => 'ignore',
            ],
            'native_function_casing' => true,
            'native_type_declaration_casing' => true,
            'no_alias_language_construct_call' => true,
            'no_alternative_syntax' => true,
            'no_binary_string' => true,
            'no_blank_lines_after_phpdoc' => true,
            'no_empty_comment' => true,
            'no_empty_phpdoc' => true,
            'no_empty_statement' => true,
            'no_extra_blank_lines' => [
                'tokens' => [
                    'attribute',
                    'case',
                    'continue',
                    'curly_brace_block',
                    'default',
                    'extra',
                    'parenthesis_brace_block',
                    'square_brace_block',
                    'switch',
                    'throw',
                    'use',
                ],
            ],
            'no_leading_namespace_whitespace' => true,
            'no_mixed_echo_print' => true,
            'no_multiline_whitespace_around_double_arrow' => true,
            'no_null_property_initialization' => true,
            'no_short_bool_cast' => true,
            'no_singleline_whitespace_before_semicolons' => true,
            'no_spaces_around_offset' => true,
            'no_superfluous_phpdoc_tags' => [
                'allow_hidden_params' => true,
                'remove_inheritdoc' => true,
            ],
            'no_trailing_comma_in_singleline' => true,
            'no_unneeded_braces' => [
                'namespaces' => true,
            ],
            'no_unneeded_control_parentheses' => [
                'statements' => [
                    'break',
                    'clone',
                    'continue',
                    'echo_print',
                    'negative_instanceof',
                    'others',
                    'return',
                    'switch_case',
                    'yield',
                    'yield_from',
                ],
            ],
            'no_unneeded_import_alias' => true,
            'no_unset_cast' => true,
            'no_unused_imports' => true,
            'no_useless_concat_operator' => true,
            'no_useless_else' => true,
            'no_useless_nullsafe_operator' => true,
            'no_useless_return' => true,
            'no_whitespace_before_comma_in_array' => ['after_heredoc' => true],
            'normalize_index_brace' => true,
            'nullable_type_declaration_for_default_null_value' => true,
            'object_operator_without_whitespace' => true,
            'operator_linebreak' => [
                'only_booleans' => true,
            ],
            'ordered_imports' => [
                'imports_order' => [
                    'class',
                    'function',
                    'const',
                ],
                'sort_algorithm' => 'alpha',
            ],
            'php_unit_fqcn_annotation' => true,
            'php_unit_method_casing' => true,
            'phpdoc_align' => true,
            'phpdoc_annotation_without_dot' => true,
            'phpdoc_indent' => true,
            'phpdoc_inline_tag_normalizer' => true,
            'phpdoc_no_access' => true,
            'phpdoc_no_alias_tag' => [
                'replacements' => [
                    'const' => 'var', // @TODO 4.0 add to @PhpdocNoAliasTagFixer defaults
                    'link' => 'see',
                    'property-read' => 'property',
                    'property-write' => 'property',
                    'type' => 'var',
                ],
            ],
            'phpdoc_no_package' => true,
            'phpdoc_no_useless_inheritdoc' => true,
            'phpdoc_order' => [
                'order' => [
                    'param',
                    'return',
                    'throws',
                ],
            ],
            'phpdoc_return_self_reference' => true,
            'phpdoc_scalar' => [
                'types' => [ // @TODO v4 drop custom config with => true, as v4 defaults are same
                    'boolean',
                    'callback',
                    'double',
                    'integer',
                    'never-return',
                    'never-returns',
                    'no-return',
                    'real',
                    'str',
                ],
            ],
            'phpdoc_separation' => [
                'groups' => [
                    ['Annotation', 'NamedArgumentConstructor', 'Target'],
                    ...PhpdocSeparationFixer::OPTION_GROUPS_DEFAULT,
                ],
                'skip_unlisted_annotations' => false, // @TODO: default value of this option changed, consider to switch to new default
            ],
            'phpdoc_single_line_var_spacing' => true,
            'phpdoc_summary' => true,
            'phpdoc_tag_type' => [
                'tags' => [
                    'inheritDoc' => 'inline',
                ],
            ],
            'phpdoc_to_comment' => [
                'allow_before_return_statement' => false, // @TODO: default value of this option changed, consider to switch to new default
            ],
            'phpdoc_trim' => true,
            'phpdoc_trim_consecutive_blank_line_separation' => true,
            'phpdoc_types' => true,
            'phpdoc_types_order' => [
                'null_adjustment' => 'always_last',
                'sort_algorithm' => 'none',
            ],
            'phpdoc_var_annotation_correct_order' => true,
            'phpdoc_var_without_name' => true,
            'protected_to_private' => true,
            'semicolon_after_instruction' => true,
            'simple_to_complex_string_variable' => true,
            'single_import_per_statement' => true,
            'single_line_comment_spacing' => true,
            'single_line_comment_style' => [
                'comment_types' => [
                    'hash',
                ],
            ],
            'single_line_empty_body' => false, // overrides @PER-CS2.0
            'single_line_throw' => true,
            'single_quote' => true,
            'single_space_around_construct' => true,
            'space_after_semicolon' => [
                'remove_in_empty_for_expressions' => true,
            ],
            'standardize_increment' => true,
            'standardize_not_equals' => true,
            'statement_indentation' => [
                'stick_comment_to_next_continuous_control_statement' => true,
            ],
            'switch_continue_to_break' => true,
            'trailing_comma_in_multiline' => [
                'after_heredoc' => true,
                'elements' => [ // explicitly omit 'arguments'
                    'array_destructuring',
                    'arrays',
                    'match',
                    'parameters',
                ],
            ],
            'trim_array_spaces' => true,
            'type_declaration_spaces' => [
                'elements' => ['function', 'property'], // @TODO v4.0 and before consider to add 'constant' (default value)
            ],
            'unary_operator_spaces' => true,
            'whitespace_after_comma_in_array' => true,
            'yoda_style' => true,
        ];
    }

    public function getDescription(): string
    {
        return 'Rules that follow the official `Symfony Coding Standards <https://symfony.com/doc/current/contributing/code/standards.html>`_. Extends ``@PER-CS``.';
    }
}
