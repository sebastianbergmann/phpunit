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

namespace PhpCsFixer;

use PhpCsFixer\Fixer\FixerInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @TODO v4 convert return type here and in Config class from :self to :static, keep the $this phpdoc
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface ConfigInterface
{
    /** @internal */
    public const PHP_VERSION_SYNTAX_SUPPORTED = '8.5';

    /**
     * Returns the path to the cache file.
     *
     * @return null|non-empty-string Returns null if not using cache
     */
    public function getCacheFile(): ?string;

    /**
     * Returns the custom fixers to use.
     *
     * @return list<FixerInterface>
     */
    public function getCustomFixers(): array;

    /**
     * Returns files to scan.
     *
     * @return iterable<\SplFileInfo>
     */
    public function getFinder(): iterable;

    public function getFormat(): string;

    /**
     * Returns true if progress should be hidden.
     */
    public function getHideProgress(): bool;

    /**
     * @return non-empty-string
     */
    public function getIndent(): string;

    /**
     * @return non-empty-string
     */
    public function getLineEnding(): string;

    /**
     * Returns the name of the configuration.
     *
     * The name must be all lowercase and without any spaces.
     *
     * @return string The name of the configuration
     */
    public function getName(): string;

    /**
     * Get configured PHP executable, if any.
     *
     * @deprecated
     *
     * @TODO 4.0 remove me
     */
    public function getPhpExecutable(): ?string;

    /**
     * Check if it is allowed to run risky fixers.
     */
    public function getRiskyAllowed(): bool;

    /**
     * Get rules.
     *
     * Keys of array are names of fixers/sets, values are true/false.
     *
     * @return array<string, array<string, mixed>|bool>
     */
    public function getRules(): array;

    /**
     * Returns true if caching should be enabled.
     */
    public function getUsingCache(): bool;

    /**
     * Adds a suite of custom fixers.
     *
     * Name of custom fixer should follow `VendorName/rule_name` convention.
     *
     * @param iterable<FixerInterface> $fixers
     *
     * @return $this
     */
    public function registerCustomFixers(iterable $fixers): self;

    /**
     * Sets the path to the cache file.
     *
     * @param non-empty-string $cacheFile
     *
     * @return $this
     */
    public function setCacheFile(string $cacheFile): self;

    /**
     * @param iterable<\SplFileInfo> $finder
     *
     * @return $this
     */
    public function setFinder(iterable $finder): self;

    /**
     * @return $this
     */
    public function setFormat(string $format): self;

    /**
     * @return $this
     */
    public function setHideProgress(bool $hideProgress): self;

    /**
     * @param non-empty-string $indent
     *
     * @return $this
     */
    public function setIndent(string $indent): self;

    /**
     * @param non-empty-string $lineEnding
     *
     * @return $this
     */
    public function setLineEnding(string $lineEnding): self;

    /**
     * Set PHP executable.
     *
     * @deprecated
     *
     * @TODO 4.0 remove me
     *
     * @return $this
     */
    public function setPhpExecutable(?string $phpExecutable): self;

    /**
     * Set if it is allowed to run risky fixers.
     *
     * @return $this
     */
    public function setRiskyAllowed(bool $isRiskyAllowed): self;

    /**
     * Set rules.
     *
     * Keys of array are names of fixers or sets.
     * Value for set must be bool (turn it on or off).
     * Value for fixer may be bool (turn it on or off) or array of configuration
     * (turn it on and contains configuration for FixerInterface::configure method).
     *
     * @param array<string, array<string, mixed>|bool> $rules
     *
     * @return $this
     */
    public function setRules(array $rules): self;

    /**
     * @return $this
     */
    public function setUsingCache(bool $usingCache): self;
}
