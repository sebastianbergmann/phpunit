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

use PhpCsFixer\Fixer\PhpUnit\PhpUnitTargetVersion;
use PhpCsFixer\RuleSet\AbstractMigrationSetDefinition;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PHPUnit4x8MigrationRiskySet extends AbstractMigrationSetDefinition
{
    public function getRules(): array
    {
        return [
            '@PHPUnit4x3Migration:risky' => true,
            'php_unit_namespaced' => [
                'target' => PhpUnitTargetVersion::VERSION_4_8,
            ],
        ];
    }
}
