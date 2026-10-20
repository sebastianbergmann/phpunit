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
 * @author ntzm
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class AliasedFixerOption implements FixerOptionInterface
{
    private FixerOptionInterface $fixerOption;

    private string $alias;

    public function __construct(FixerOptionInterface $fixerOption, string $alias)
    {
        $this->fixerOption = $fixerOption;
        $this->alias = $alias;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getName(): string
    {
        return $this->fixerOption->getName();
    }

    public function getDescription(): string
    {
        return $this->fixerOption->getDescription();
    }

    public function hasDefault(): bool
    {
        return $this->fixerOption->hasDefault();
    }

    /**
     * @return mixed
     *
     * @throws \LogicException when no default value is defined
     */
    public function getDefault()
    {
        return $this->fixerOption->getDefault();
    }

    public function getAllowedTypes(): ?array
    {
        return $this->fixerOption->getAllowedTypes();
    }

    public function getAllowedValues(): ?array
    {
        return $this->fixerOption->getAllowedValues();
    }

    public function getNormalizer(): ?\Closure
    {
        return $this->fixerOption->getNormalizer();
    }
}
