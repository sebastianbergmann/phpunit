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

namespace PhpCsFixer\Config;

/**
 * EXPERIMENTAL: This feature is experimental and does not fall under the backward compatibility promise.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NullRuleCustomisationPolicy implements RuleCustomisationPolicyInterface
{
    /**
     * @internal
     */
    public const VERSION_FOR_CACHE = 'null-policy';

    public function getPolicyVersionForCache(): string
    {
        return self::VERSION_FOR_CACHE;
    }

    public function getRuleCustomisers(): array
    {
        return [];
    }
}
