<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers\Fixer;

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Preg;

/**
 * @no-named-arguments
 */
abstract class AbstractFixer implements FixerInterface
{
    final public static function name(): string
    {
        $name = Preg::replace('/(?<!^)(?=[A-Z])/', '_', \substr(static::class, 29, -5));

        return 'PhpCsFixerCustomFixers/' . \strtolower($name);
    }

    final public function getName(): string
    {
        return self::name();
    }

    final public function supports(\SplFileInfo $file): bool
    {
        return true;
    }
}
