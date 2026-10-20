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
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FixerOptionBuilder
{
    private string $name;

    private string $description;

    /**
     * @var null|mixed
     */
    private $default;

    private bool $isRequired = true;

    /**
     * @var null|list<string>
     */
    private ?array $allowedTypes = null;

    /**
     * @var null|non-empty-list<null|(callable(mixed): bool)|scalar>
     */
    private ?array $allowedValues = null;

    private ?\Closure $normalizer = null;

    private ?string $deprecationMessage = null;

    public function __construct(string $name, string $description)
    {
        $this->name = $name;
        $this->description = $description;
        $this->default = null;
    }

    /**
     * @param mixed $default
     *
     * @return $this
     */
    public function setDefault($default): self
    {
        $this->default = $default;
        $this->isRequired = false;

        return $this;
    }

    /**
     * @param list<string> $allowedTypes
     *
     * @return $this
     */
    public function setAllowedTypes(array $allowedTypes): self
    {
        $this->allowedTypes = $allowedTypes;

        return $this;
    }

    /**
     * @param non-empty-list<null|(callable(mixed): bool)|scalar> $allowedValues
     *
     * @return $this
     */
    public function setAllowedValues(array $allowedValues): self
    {
        $this->allowedValues = $allowedValues;

        return $this;
    }

    /**
     * @return $this
     */
    public function setNormalizer(\Closure $normalizer): self
    {
        $this->normalizer = $normalizer;

        return $this;
    }

    /**
     * @return $this
     */
    public function setDeprecationMessage(?string $deprecationMessage): self
    {
        $this->deprecationMessage = $deprecationMessage;

        return $this;
    }

    public function getOption(): FixerOptionInterface
    {
        $option = new FixerOption(
            $this->name,
            $this->description,
            $this->isRequired,
            $this->default,
            $this->allowedTypes,
            $this->allowedValues,
            $this->normalizer,
        );

        if (null !== $this->deprecationMessage) {
            $option = new DeprecatedFixerOption($option, $this->deprecationMessage);
        }

        return $option;
    }
}
