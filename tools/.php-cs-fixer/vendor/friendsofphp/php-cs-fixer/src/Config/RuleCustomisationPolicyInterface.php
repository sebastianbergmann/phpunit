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

use PhpCsFixer\Fixer\FixerInterface;

/**
 * EXPERIMENTAL: This feature is experimental and does not fall under the backward compatibility promise.
 *
 * @todo v3.999 replace \SplFileInfo with \Symfony\Component\Finder\SplFileInfo
 *
 * @phpstan-type _RuleCustomisationPolicyCallback \Closure(\SplFileInfo): (bool|FixerInterface)
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface RuleCustomisationPolicyInterface
{
    /**
     * Returns a string that changes when the policy implementation changes in a way that
     * would affect the cache validity.
     *
     * @example you may use the following snippet if your policy does not depend on any code outside of the file
     *          `return hash_file(\PHP_VERSION_ID >= 8_01_00 ? 'xxh128' : 'md5', __FILE__);`
     *
     * @return non-empty-string
     */
    public function getPolicyVersionForCache(): string;

    /**
     * Customise fixers for given files.
     *
     * Array keys are fixer names, values are closures that will be invoked before applying the fixer to a specific file.
     * The closures receive the file as argument and must return:
     * - true to apply the fixer as is to the file
     * - false to skip applying the fixer to the file
     * - a new fixer instance to apply a customised version of the fixer
     *
     * When PHP CS Fixer is about to start fixing files, it will check that the currently used fixers include at least
     * all the fixers for which customisation rules are defined. If a customiser is defined for a fixer that is not currently applied,
     * an exception will be thrown.
     * This ensures that customisers are actually used for expected fixerswhich may be replaced by newer fixers in newer versions of PHP CS Fixer.
     * Since fixer sets may change even in patch releases, this also means that your implementation of this interface may need to be updated accordingly, even in patch releases.
     * So, we can't guarantee semver compatibility for Rule Customisation Policies.
     *
     * @example
     * ```
     * [
     *     'array_syntax' => static function (\SplFileInfo $file) {
     *         if (str_ends_with($file->getPathname(), '/tests/foo.php')) {
     *             // Disable the fixer for the file tests/foo.php
     *             return false;
     *         }
     *         if (str_ends_with($file->getPathname(), '/bin/entrypoint')) {
     *             // For the file bin/entrypoint let's create a new fixer instance with a different configuration
     *             $fixer = new ArraySyntaxFixer();
     *             $fixer->configure(['syntax' => 'long']);
     *             return $fixer;
     *         }
     *         // Keep the default configuration for other files
     *         return true;
     *     },
     * ]
     * ```
     *
     * @return array<non-empty-string, _RuleCustomisationPolicyCallback>
     */
    public function getRuleCustomisers(): array;
}
