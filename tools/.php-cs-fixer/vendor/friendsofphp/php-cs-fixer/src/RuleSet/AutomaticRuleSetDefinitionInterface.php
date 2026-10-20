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

/**
 * Used to indicate that the ruleset can be automatically determined and will differ based on runtime conditions.
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface AutomaticRuleSetDefinitionInterface
{
    public const WARNING_MESSAGE_DECORATED = '<comment>This rule is automatic</comment>: it\'s definition depends on your project, eg "composer.json" file.';

    /**
     * @return array<string, array<string, mixed>|bool>
     */
    public function getRulesCandidates(): array;
}
