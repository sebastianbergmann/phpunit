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
final class PHP7x4MigrationSet extends AbstractMigrationSetDefinition
{
    public function getRules(): array
    {
        return [
            '@PHP7x3Migration' => true,
            'assign_null_coalescing_to_coalesce_equal' => true,
            'normalize_index_brace' => true,
            'short_scalar_cast' => true,
        ];
    }
}
