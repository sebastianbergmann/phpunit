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

/**
 * @internal
 *
 * PER Coding Style v1.0.
 *
 * @see https://github.com/php-fig/per-coding-style/blob/1.0.0/spec.md
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PERCS10Set extends AbstractRuleSetDefinition implements DeprecatedRuleSetDefinitionInterface
{
    public function getName(): string
    {
        return '@PER-CS1.0';
    }

    public function getRules(): array
    {
        return $this->getProxiedSet()->getRules();
    }

    public function getDescription(): string
    {
        return $this->getProxiedSet()->getDescription();
    }

    public function getSuccessorsNames(): array
    {
        return [$this->getProxiedSet()->getName()];
    }

    private function getProxiedSet(): RuleSetDefinitionInterface
    {
        return new PERCS1x0Set();
    }
}
