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
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FileSpecificCodeSample implements FileSpecificCodeSampleInterface
{
    private CodeSampleInterface $codeSample;

    private \SplFileInfo $splFileInfo;

    /**
     * @param null|array<string, mixed> $configuration
     */
    public function __construct(
        string $code,
        \SplFileInfo $splFileInfo,
        ?array $configuration = null
    ) {
        $this->codeSample = new CodeSample($code, $configuration);
        $this->splFileInfo = $splFileInfo;
    }

    public function getCode(): string
    {
        return $this->codeSample->getCode();
    }

    public function getConfiguration(): ?array
    {
        return $this->codeSample->getConfiguration();
    }

    public function getSplFileInfo(): \SplFileInfo
    {
        return $this->splFileInfo;
    }
}
