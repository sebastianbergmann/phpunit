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
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface RuleSetDefinitionInterface
{
    public function getDescription(): string;

    public function getName(): string;

    /**
     * Get all rules from rules set.
     *
     * @return array<string, array<string, mixed>|bool>
     */
    public function getRules(): array;

    public function isRisky(): bool;
}
