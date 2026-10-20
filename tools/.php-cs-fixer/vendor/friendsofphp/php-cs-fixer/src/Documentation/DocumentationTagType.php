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

namespace PhpCsFixer\Documentation;

/**
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class DocumentationTagType
{
    public const EXPERIMENTAL = 'experimental';
    public const INTERNAL = 'internal';
    public const DEPRECATED = 'deprecated';
    public const RISKY = 'risky';
    public const CONFIGURABLE = 'configurable';
    public const AUTOMATIC = 'automatic';

    private function __construct() {}
}
