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

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class FixerNameValidator
{
    public function isValid(string $name, bool $isCustom): bool
    {
        if (!$isCustom) {
            return Preg::match('/^[a-z][a-z0-9_]*$/', $name);
        }

        return Preg::match('/^[A-Z][a-zA-Z0-9]*\/[a-z][a-z0-9_]*$/', $name);
    }
}
