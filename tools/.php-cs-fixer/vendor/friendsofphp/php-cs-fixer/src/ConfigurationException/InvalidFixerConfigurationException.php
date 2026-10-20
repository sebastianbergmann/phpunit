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

namespace PhpCsFixer\ConfigurationException;

use PhpCsFixer\Console\Command\FixCommandExitStatusCalculator;

/**
 * Exception thrown by Fixers on misconfiguration.
 *
 * @internal
 *
 * @final Only internal extending this class is supported
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
class InvalidFixerConfigurationException extends InvalidConfigurationException
{
    private string $fixerName;

    public function __construct(string $fixerName, string $message, ?\Throwable $previous = null)
    {
        parent::__construct(
            \sprintf('[%s] %s', $fixerName, $message),
            FixCommandExitStatusCalculator::EXIT_STATUS_FLAG_HAS_INVALID_FIXER_CONFIG,
            $previous,
        );

        $this->fixerName = $fixerName;
    }

    public function getFixerName(): string
    {
        return $this->fixerName;
    }
}
