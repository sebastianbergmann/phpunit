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

use PhpCsFixer\RuleSet\AbstractMigrationSetDefinition;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PHP7x0MigrationRiskySet extends AbstractMigrationSetDefinition
{
    public function getRules(): array
    {
        return [
            '@PHP5x6Migration:risky' => true,
            'combine_nested_dirname' => true,
            'declare_strict_types' => true,
            'non_printable_character' => true,
            'random_api_migration' => [ // @TODO v4 replace with `true`
                'replacements' => [
                    'mt_getrandmax' => 'getrandmax',
                    'mt_rand' => 'random_int',
                    'mt_srand' => 'srand',
                    'rand' => 'random_int',
                ],
            ],
        ];
    }
}
