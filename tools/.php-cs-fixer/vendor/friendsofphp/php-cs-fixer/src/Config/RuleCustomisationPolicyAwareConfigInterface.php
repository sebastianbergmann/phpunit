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

use PhpCsFixer\ConfigInterface;

/**
 * EXPERIMENTAL: This feature is experimental and does not fall under the backward compatibility promise.
 *
 * @TODO 4.0 Include support for this in main ConfigInterface
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface RuleCustomisationPolicyAwareConfigInterface extends ConfigInterface
{
    /**
     * EXPERIMENTAL: This feature is experimental and does not fall under the backward compatibility promise.
     * Registers a filter to be applied to fixers right before running them.
     *
     * @todo v4 Introduce it in main ConfigInterface
     *
     * @return $this
     */
    public function setRuleCustomisationPolicy(?RuleCustomisationPolicyInterface $ruleCustomisationPolicy): ConfigInterface;

    /**
     * EXPERIMENTAL: This feature is experimental and does not fall under the backward compatibility promise.
     * Gets the filter to be applied to fixers right before running them.
     *
     * @todo v4 Introduce it in main ConfigInterface
     */
    public function getRuleCustomisationPolicy(): ?RuleCustomisationPolicyInterface;
}
