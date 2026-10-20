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

namespace PhpCsFixer\Fixer\ArrayNotation;

use PhpCsFixer\AbstractProxyFixer;
use PhpCsFixer\Fixer\Basic\NoTrailingCommaInSinglelineFixer;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;

/**
 * @deprecated
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoTrailingCommaInSinglelineArrayFixer extends AbstractProxyFixer implements DeprecatedFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'PHP single-line arrays should not have trailing comma.',
            [new CodeSample("<?php\n\$a = array('sample',  );\n")],
        );
    }

    public function getSuccessorsNames(): array
    {
        return array_keys($this->proxyFixers);
    }

    protected function createProxyFixers(): array
    {
        $fixer = new NoTrailingCommaInSinglelineFixer();
        $fixer->configure(['elements' => ['array']]);

        return [$fixer];
    }
}
