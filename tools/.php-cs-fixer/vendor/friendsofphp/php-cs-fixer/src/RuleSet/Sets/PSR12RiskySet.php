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

namespace PhpCsFixer\RuleSet\Sets;

use PhpCsFixer\RuleSet\AbstractRuleSetDefinition;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class PSR12RiskySet extends AbstractRuleSetDefinition
{
    public function getRules(): array
    {
        return [
            'no_trailing_whitespace_in_string' => true,
            'no_unreachable_default_argument_value' => true,
        ];
    }

    public function getDescription(): string
    {
        return 'Rules that follow `PSR-12 <https://www.php-fig.org/psr/psr-12/>`_ standard.';
    }
}
