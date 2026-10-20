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
use PhpCsFixer\RuleSet\DeprecatedRuleSetDefinitionInterface;

/**
 * @internal
 *
 * @deprecated use `@PER-CS:risky` instead
 *
 * @TODO 4.0 remove me
 *
 * Last updated to PER Coding Style v2.0.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PERRiskySet extends AbstractRuleSetDefinition implements DeprecatedRuleSetDefinitionInterface
{
    public function getName(): string
    {
        return '@PER:risky';
    }

    public function getRules(): array
    {
        return [
            '@PER-CS:risky' => true,
        ];
    }

    public function getDescription(): string
    {
        return 'Alias for the newest ``PER-CS`` risky rules. It is recommended you use ``@PER-CS2.0:risky`` instead if you want to stick with stable ruleset.';
    }

    public function getSuccessorsNames(): array
    {
        return ['@PER-CS:risky'];
    }
}
