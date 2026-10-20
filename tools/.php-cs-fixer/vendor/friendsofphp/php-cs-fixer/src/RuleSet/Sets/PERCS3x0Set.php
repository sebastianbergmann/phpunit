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
 * PER Coding Style v3.0.
 *
 * @see https://github.com/php-fig/per-coding-style/blob/3.0.0/spec.md
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PERCS3x0Set extends AbstractRuleSetDefinition
{
    public function getName(): string
    {
        return '@PER-CS3x0';
    }

    public function getRules(): array
    {
        return [
            '@PER-CS2x0' => true,
            'nullable_type_declaration' => true,
            'operator_linebreak' => true,
            'ordered_types' => [
                'null_adjustment' => 'always_last',
                'sort_algorithm' => 'none',
            ],
            'single_class_element_per_statement' => true,
            'types_spaces' => true,
        ];
    }

    public function getDescription(): string
    {
        return 'Rules that follow `PER Coding Style 3.0 <https://www.php-fig.org/per/coding-style/>`_.';
    }
}
