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

namespace PhpCsFixer\FixerConfiguration;

/**
 * @readonly
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class DeprecatedFixerOption implements DeprecatedFixerOptionInterface
{
    private FixerOptionInterface $option;

    private string $deprecationMessage;

    public function __construct(FixerOptionInterface $option, string $deprecationMessage)
    {
        $this->option = $option;
        $this->deprecationMessage = $deprecationMessage;
    }

    public function getName(): string
    {
        return $this->option->getName();
    }

    public function getDescription(): string
    {
        return $this->option->getDescription();
    }

    public function hasDefault(): bool
    {
        return $this->option->hasDefault();
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->option->getDefault();
    }

    public function getAllowedTypes(): ?array
    {
        return $this->option->getAllowedTypes();
    }

    public function getAllowedValues(): ?array
    {
        return $this->option->getAllowedValues();
    }

    public function getNormalizer(): ?\Closure
    {
        return $this->option->getNormalizer();
    }

    public function getDeprecationMessage(): string
    {
        return $this->deprecationMessage;
    }
}
