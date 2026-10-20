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
final class PhpCsFixerRiskySet extends AbstractRuleSetDefinition
{
    public function getRules(): array
    {
        return [
            '@PER-CS:risky' => true,
            '@Symfony:risky' => true,
            'comment_to_phpdoc' => true,
            'declare_strict_types' => true,
            'final_internal_class' => true,
            'get_class_to_class_keyword' => false,
            'modernize_strpos' => false,
            // @TODO: consider switching to `true`, like in @Symfony
            'native_constant_invocation' => [
                'fix_built_in' => false,
                'include' => [
                    'DIRECTORY_SEPARATOR',
                    'PHP_INT_SIZE',
                    'PHP_SAPI',
                    'PHP_VERSION_ID',
                ],
                'scope' => 'namespaced',
                'strict' => true,
            ],
            'no_alias_functions' => [
                'sets' => [
                    '@all',
                ],
            ],
            'no_trailing_whitespace_in_string' => true, // override Symfony to mimics PER / CS
            'no_unset_on_property' => true,
            'php_unit_data_provider_name' => true,
            'php_unit_data_provider_return_type' => true,
            'php_unit_data_provider_static' => ['force' => true],
            'php_unit_strict' => true,
            'php_unit_test_case_static_method_calls' => ['call_type' => 'self'],
            'strict_comparison' => true,
            'strict_param' => true,
            'void_return' => true, // override Symfony
            'yield_from_array_to_yields' => true,
        ];
    }

    public function getDescription(): string
    {
        return 'Rules recommended by ``PHP CS Fixer`` team, highly opinionated. Extends ``@PER-CS:risky`` and ``@Symfony:risky``.';
    }
}
