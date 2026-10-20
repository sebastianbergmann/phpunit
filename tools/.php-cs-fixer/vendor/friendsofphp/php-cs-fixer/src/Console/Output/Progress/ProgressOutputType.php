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

namespace PhpCsFixer\Console\Output\Progress;

/**
 * @TODO PHP 8.1 switch this and similar classes to ENUM
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ProgressOutputType
{
    public const NONE = 'none';
    public const DOTS = 'dots';
    public const BAR = 'bar';

    /**
     * @return non-empty-list<ProgressOutputType::*>
     */
    public static function all(): array
    {
        return [
            self::BAR,
            self::DOTS,
            self::NONE,
        ];
    }
}
