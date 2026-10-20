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

namespace PhpCsFixer\RuleSet;

use PhpCsFixer\Preg;

/**
 * @internal
 *
 * @TODO v4 remove me @MARKER_deprecated_migration_ruleset
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
abstract class AbstractMajorMinorDeprecationSetDefinition extends AbstractMigrationSetDefinition implements DeprecatedRuleSetDefinitionInterface
{
    public function getRules(): array
    {
        $newName = Preg::replace('#(\d+)\.?(\d)#', '\1x\2', $this->getName());

        return [
            $newName => true,
        ];
    }

    public function getSuccessorsNames(): array
    {
        return array_keys($this->getRules());
    }
}
