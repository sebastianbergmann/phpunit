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

namespace PhpCsFixer\Fixer\Basic;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Fixer for rules defined in PSR1 ¶2.2.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class EncodingFixer extends AbstractFixer
{
    private string $bom;

    public function __construct()
    {
        parent::__construct();

        $this->bom = pack('CCC', 0xEF, 0xBB, 0xBF);
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'PHP code MUST use only UTF-8 without BOM (remove BOM).',
            [
                new CodeSample(
                    <<<PHP
                        {$this->bom}<?php

                        echo "Hello!";

                        PHP,
                ),
            ],
        );
    }

    public function getPriority(): int
    {
        // must run first (at least before Fixers that using Tokens) - for speed reason of whole fixing process
        return 100;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return true;
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        $content = $tokens[0]->getContent();

        if (str_starts_with($content, $this->bom)) {
            $newContent = substr($content, 3);

            if ('' === $newContent) {
                $tokens->clearAt(0);
            } else {
                $tokens[0] = new Token([$tokens[0]->getId(), $newContent]);
            }
        }
    }
}
