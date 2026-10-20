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
 * Set of rules to be used by fixer.
 *
 * Example of set: ["@PSR2" => true, "@PSR1" => false, "strict" => true].
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface RuleSetInterface
{
    /**
     * @param array<string, array<string, mixed>|bool> $set
     */
    public function __construct(array $set = []);

    /**
     * Get configuration for given rule.
     *
     * @return null|array<string, mixed>
     */
    public function getRuleConfiguration(string $rule): ?array;

    /**
     * Get all rules from rules set.
     *
     * @return array<string, array<string, mixed>|true>
     */
    public function getRules(): array;

    /**
     * Check given rule is in rules set.
     */
    public function hasRule(string $rule): bool;
}
