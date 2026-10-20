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
use PhpCsFixer\RuleSet\RuleSetDefinitionInterface;
use PhpCsFixer\RuleSet\RuleSets;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PERCSSet extends AbstractRuleSetDefinition
{
    public function getName(): string
    {
        return '@PER-CS';
    }

    public function getRules(): array
    {
        return [
            $this->getHighestPerCsSet()->getName() => true,
        ];
    }

    public function getDescription(): string
    {
        return 'Rules that follow `PER Coding Style <https://www.php-fig.org/per/coding-style/>`_, Set is an alias for the latest revision of ``PER-CS`` rules - use it if you always want to be in sync with newest ``PER-CS`` standard.';
    }

    private function getHighestPerCsSet(): RuleSetDefinitionInterface
    {
        static $set = null;

        if (null === $set) {
            $currentSet = $this;

            $sets = array_filter(
                RuleSets::getSetDefinitions(),
                static fn (RuleSetDefinitionInterface $set): bool => !($set instanceof DeprecatedRuleSetDefinitionInterface)
                    && $set->isRisky() === $currentSet->isRisky()
                    && $set->getName() !== $currentSet->getName()
                    && str_starts_with($set->getName(), $currentSet->getName()),
            );

            ksort($sets, \SORT_NATURAL);

            $set = array_pop($sets);
        }

        return $set;
    }
}
