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

use Symfony\Component\Finder\Finder;

/**
 * Collection of Transformer classes.
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class Transformers
{
    /**
     * The registered transformers.
     *
     * @var list<TransformerInterface>
     */
    private array $items = [];

    /**
     * Register built in Transformers.
     */
    private function __construct()
    {
        $this->registerBuiltInTransformers();

        usort($this->items, static fn (TransformerInterface $a, TransformerInterface $b): int => $b->getPriority() <=> $a->getPriority());
    }

    public static function createSingleton(): self
    {
        static $instance = null;

        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Transform given Tokens collection through all Transformer classes.
     *
     * @param Tokens $tokens Tokens collection
     */
    public function transform(Tokens $tokens): void
    {
        foreach ($this->items as $transformer) {
            if (!$transformer->isCandidate($tokens)) {
                continue;
            }

            $transformer->process($tokens);
        }

        $tokens->clearEmptyTokens();
    }

    /**
     * @param TransformerInterface $transformer Transformer
     */
    private function registerTransformer(TransformerInterface $transformer): void
    {
        if (\PHP_VERSION_ID >= $transformer->getRequiredPhpVersionId()) {
            $this->items[] = $transformer;
        }
    }

    private function registerBuiltInTransformers(): void
    {
        static $registered = false;

        if ($registered) {
            return;
        }

        $registered = true;

        foreach ($this->findBuiltInTransformers() as $transformer) {
            $this->registerTransformer($transformer);
        }
    }

    /**
     * @return iterable<TransformerInterface>
     */
    private function findBuiltInTransformers(): iterable
    {
        foreach (Finder::create()->files()->in(__DIR__.'/Transformer') as $file) {
            $relativeNamespace = $file->getRelativePath();
            $class = __NAMESPACE__.'\Transformer\\'.('' !== $relativeNamespace ? $relativeNamespace.'\\' : '').$file->getBasename('.php');

            $instance = new $class();

            \assert($instance instanceof TransformerInterface);

            yield $instance;
        }
    }
}
