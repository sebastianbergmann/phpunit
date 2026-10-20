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

namespace PhpCsFixer\Fixer;

use PhpCsFixer\ConfigurationException\InvalidFixerConfigurationException;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;

/**
 * @template TFixerInputConfig of array<string, mixed>
 * @template TFixerComputedConfig of array<string, mixed>
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface ConfigurableFixerInterface extends FixerInterface
{
    /**
     * Set configuration.
     *
     * New configuration must override current one, not patch it.
     * Using empty array makes fixer to use default configuration
     * (or reset configuration from previously configured back to default one).
     *
     * Some fixers may have no configuration, then - simply don't implement this interface.
     * Other ones may have configuration that will change behaviour of fixer,
     * eg `php_unit_strict` fixer allows to configure which methods should be fixed.
     * Finally, some fixers need configuration to work, eg `header_comment`.
     *
     * @param TFixerInputConfig $configuration configuration depends on Fixer
     *
     * @throws InvalidFixerConfigurationException
     */
    public function configure(array $configuration): void;

    /**
     * Defines the available configuration options of the fixer.
     */
    public function getConfigurationDefinition(): FixerConfigurationResolverInterface;
}
