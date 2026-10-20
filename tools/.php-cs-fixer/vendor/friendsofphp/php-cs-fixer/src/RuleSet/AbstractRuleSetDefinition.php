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
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
abstract class AbstractRuleSetDefinition implements RuleSetDefinitionInterface
{
    public function __construct() {}

    public function getName(): string
    {
        $name = substr(static::class, 1 + (int) strrpos(static::class, '\\'), -3);

        return '@'.str_replace('Risky', ':risky', $name);
    }

    public function isRisky(): bool
    {
        return str_contains(static::class, 'Risky');
    }
}
