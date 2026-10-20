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

namespace PhpCsFixer\Compat\Symfony\Component\Console\Style;

if (method_exists(\Symfony\Component\Console\Style\SymfonyStyle::class, 'outlineSuccess')) { // @phpstan-ignore function.alreadyNarrowedType
    /**
     * @internal
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
     */
    final class SymfonyStyle extends \Symfony\Component\Console\Style\SymfonyStyle {}
} else {
    /**
     * @internal
     *
     * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
     *
     * @phpstan-ignore class.extendsFinalByPhpDoc
     */
    final class SymfonyStyle extends SymfonyStyleCompat {}
}
