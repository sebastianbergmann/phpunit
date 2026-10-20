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

namespace PhpCsFixer\Cache;

/**
 * @author Andreas Möller <am@localheinz.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface SignatureInterface
{
    public function getPhpVersion(): string;

    public function getFixerVersion(): string;

    public function getIndent(): string;

    public function getLineEnding(): string;

    /**
     * @return array<string, array<string, mixed>|bool>
     */
    public function getRules(): array;

    public function getRuleCustomisationPolicyVersion(): string;

    public function equals(self $signature): bool;
}
