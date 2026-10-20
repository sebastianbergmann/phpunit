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

namespace PhpCsFixer\Tokenizer;

use PhpCsFixer\Utils;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
abstract class AbstractTransformer implements TransformerInterface
{
    public function getName(): string
    {
        $nameParts = explode('\\', static::class);
        $name = substr(end($nameParts), 0, -\strlen('Transformer'));

        return Utils::camelCaseToUnderscore($name);
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function process(Tokens $tokens): void
    {
        if (!method_exists($this, 'processToken')) {
            throw new \LogicException(\sprintf('Transformer "%s" must provide own "process(Tokens $tokens)" method (preferred) or "processToken(Tokens $tokens, Token $token, int $index)" method (deprecated).', static::class));
        }

        foreach ($tokens as $index => $token) {
            $this->processToken($tokens, $token, $index);
        }
    }

    abstract public function getCustomTokens(): array;

    // @deprecated override `process(Tokens $tokens)` instead
    // abstract public function processToken(Tokens $tokens, Token $token, int $index): void;
}
