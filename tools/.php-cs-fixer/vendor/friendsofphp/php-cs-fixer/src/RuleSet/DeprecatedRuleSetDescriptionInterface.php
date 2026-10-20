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

namespace PhpCsFixer\RuleSet;

/*
 * @deprecated
 * @TODO v4 remove me @MARKER_deprecated_DeprecatedRuleSetDescriptionInterface
 */
class_alias(
    DeprecatedRuleSetDefinitionInterface::class,
    DeprecatedRuleSetDescriptionInterface::class, // @phpstan-ignore class.notFound
);
