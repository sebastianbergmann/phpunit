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

namespace PhpCsFixer\FixerDefinition;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface FixerDefinitionInterface
{
    public function getSummary(): string;

    public function getDescription(): ?string;

    /**
     * @return null|string null for non-risky fixer
     */
    public function getRiskyDescription(): ?string;

    /**
     * Array of samples, where single sample is [code, configuration].
     *
     * @return list<CodeSampleInterface>
     */
    public function getCodeSamples(): array;
}
