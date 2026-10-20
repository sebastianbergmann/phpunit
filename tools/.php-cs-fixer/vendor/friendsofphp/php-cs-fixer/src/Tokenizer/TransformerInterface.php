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

/**
 * Interface for Transformer class.
 *
 * Transformer role is to register custom tokens and transform Tokens collection to use them.
 *
 * Custom token is a user defined token type and is used to separate different meaning of original token type.
 * For example T_ARRAY is a token for both creating new array and typehinting a parameter. This two meaning should have two token types.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
interface TransformerInterface
{
    /**
     * Get tokens created by Transformer.
     *
     * @return list<int>
     */
    public function getCustomTokens(): array;

    /**
     * Return the name of the transformer.
     *
     * The name must be all lowercase and without any spaces.
     *
     * @return string The name of the fixer
     */
    public function getName(): string;

    /**
     * Returns the priority of the transformer.
     *
     * The default priority is 0 and higher priorities are executed first.
     */
    public function getPriority(): int;

    /**
     * Return minimal required PHP version id to transform the code.
     *
     * Custom Token kinds from Transformers are always registered, but sometimes
     * there is no need to analyse the Tokens if for sure we cannot find examined
     * token kind, e.g. transforming `T_FUNCTION` in `<?php use function Foo\\bar;`
     * code.
     */
    public function getRequiredPhpVersionId(): int;

    /**
     * Returns whether this transformer should act upon this token collection.
     *
     * @see FixerInterface::isCandidate()
     */
    public function isCandidate(Tokens $tokens): bool;

    /**
     * Process Token to transform it into custom token when needed.
     */
    public function process(Tokens $tokens): void;
}
