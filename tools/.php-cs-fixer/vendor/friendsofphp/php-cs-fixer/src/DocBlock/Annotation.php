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

namespace PhpCsFixer\DocBlock;

use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceAnalysis;
use PhpCsFixer\Tokenizer\Analyzer\Analysis\NamespaceUseAnalysis;

/**
 * This represents an entire annotation from a docblock.
 *
 * @author Graham Campbell <hello@gjcampbell.co.uk>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class Annotation implements \Stringable
{
    /**
     * All the annotation tag names with types.
     *
     * @var non-empty-list<string>
     */
    public const TAGS_WITH_TYPES = [
        'extends',
        'implements',
        'method',
        'param',
        'param-out',
        'phpstan-import-type',
        'phpstan-type',
        'phpstan-var',
        'property',
        'property-read',
        'property-write',
        'psalm-import-type',
        'psalm-type',
        'psalm-var',
        'return',
        'throws',
        'type',
        'var',
    ];

    /**
     * The lines that make up the annotation.
     *
     * @var non-empty-list<Line>
     */
    private array $lines;

    /**
     * The position of the first line of the annotation in the docblock.
     */
    private int $start;

    /**
     * The position of the last line of the annotation in the docblock.
     */
    private int $end;

    /**
     * The associated tag.
     */
    private ?Tag $tag = null;

    /**
     * Lazy loaded, cached types content.
     */
    private ?string $typesContent = null;

    /**
     * The cached types.
     *
     * @var null|list<string>
     */
    private ?array $types = null;

    private ?NamespaceAnalysis $namespace = null;

    /**
     * @var list<NamespaceUseAnalysis>
     */
    private array $namespaceUses;

    /**
     * Create a new line instance.
     *
     * @param non-empty-array<int, Line> $lines
     * @param null|NamespaceAnalysis     $namespace
     * @param list<NamespaceUseAnalysis> $namespaceUses
     */
    public function __construct(array $lines, $namespace = null, array $namespaceUses = [])
    {
        $this->lines = array_values($lines);
        $this->namespace = $namespace;
        $this->namespaceUses = $namespaceUses;

        $this->start = array_key_first($lines);
        $this->end = array_key_last($lines);
    }

    /**
     * Get the string representation of object.
     */
    public function __toString(): string
    {
        return $this->getContent();
    }

    /**
     * Get all the annotation tag names with types.
     *
     * @return non-empty-list<string>
     *
     * @deprecated Use `Annotation::TAGS_WITH_TYPES` constant instead
     *
     * @TODO 4.0 remove me
     */
    public static function getTagsWithTypes(): array
    {
        return self::TAGS_WITH_TYPES;
    }

    /**
     * Get the start position of this annotation.
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * Get the end position of this annotation.
     */
    public function getEnd(): int
    {
        return $this->end;
    }

    /**
     * Get the associated tag.
     */
    public function getTag(): Tag
    {
        if (null === $this->tag) {
            $this->tag = new Tag($this->lines[0]);
        }

        return $this->tag;
    }

    /**
     * @internal
     */
    public function getTypeExpression(): ?TypeExpression
    {
        $typesContent = $this->getTypesContent();

        return null === $typesContent
            ? null
            : new TypeExpression($typesContent, $this->namespace, $this->namespaceUses);
    }

    /**
     * @internal
     */
    public function getVariableName(): ?string
    {
        $type = preg_quote($this->getTypesContent() ?? '', '/');
        $regex = \sprintf(
            '/@%s\s+(%s\s*)?(&\s*)?(\.{3}\s*)?(?<variable>\$%s)(?:.*|$)/',
            $this->tag->getName(),
            $type,
            TypeExpression::REGEX_IDENTIFIER,
        );

        if (Preg::match($regex, $this->getContent(), $matches)) {
            \assert(isset($matches['variable']));

            return $matches['variable'];
        }

        return null;
    }

    /**
     * Get the types associated with this annotation.
     *
     * @return list<string>
     */
    public function getTypes(): array
    {
        if (null === $this->types) {
            $typeExpression = $this->getTypeExpression();
            $this->types = null === $typeExpression
                ? []
                : $typeExpression->getTypes();
        }

        return $this->types;
    }

    /**
     * Set the types associated with this annotation.
     *
     * @param list<string> $types
     */
    public function setTypes(array $types): void
    {
        $origTypesContent = $this->getTypesContent();
        $newTypesContent = implode(
            // Fallback to union type is provided for backward compatibility (previously glue was set to `|` by default even when type was not composite)
            // @TODO Better handling for cases where type is fixed (original type is not composite, but was made composite during fix)
            $this->getTypeExpression()->getTypesGlue() ?? '|',
            $types,
        );

        if ($origTypesContent === $newTypesContent) {
            return;
        }

        $originalTypesLines = Preg::split('/([^\n\r]+\R*)/', $origTypesContent, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE);
        $newTypesLines = Preg::split('/([^\n\r]+\R*)/', $newTypesContent, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE);

        \assert(\count($originalTypesLines) === \count($newTypesLines));

        foreach ($newTypesLines as $index => $line) {
            \assert(isset($originalTypesLines[$index]));
            $pattern = '/'.preg_quote($originalTypesLines[$index], '/').'/';

            \assert(isset($this->lines[$index]));
            $this->lines[$index]->setContent(Preg::replace($pattern, $line, $this->lines[$index]->getContent(), 1));
        }

        $this->clearCache();
    }

    /**
     * Get the normalized types associated with this annotation, so they can easily be compared.
     *
     * @return list<string>
     */
    public function getNormalizedTypes(): array
    {
        $typeExpression = $this->getTypeExpression();
        if (null === $typeExpression) {
            return [];
        }

        $normalizedTypeExpression = $typeExpression
            ->mapTypes(static fn (TypeExpression $v) => new TypeExpression(strtolower($v->toString()), null, []))
            ->sortTypes(static fn (TypeExpression $a, TypeExpression $b) => $a->toString() <=> $b->toString())
        ;

        return $normalizedTypeExpression->getTypes();
    }

    /**
     * Remove this annotation by removing all its lines.
     */
    public function remove(): void
    {
        foreach ($this->lines as $line) {
            if ($line->isTheStart() && $line->isTheEnd()) {
                // Single line doc block, remove entirely
                $line->remove();
            } elseif ($line->isTheStart()) {
                // Multi line doc block, but start is on the same line as the first annotation, keep only the start
                $content = Preg::replace('#(\s*/\*\*).*#', '$1', $line->getContent());

                $line->setContent($content);
            } elseif ($line->isTheEnd()) {
                // Multi line doc block, but end is on the same line as the last annotation, keep only the end
                $content = Preg::replace('#(\s*)\S.*(\*/.*)#', '$1$2', $line->getContent());

                $line->setContent($content);
            } else {
                // Multi line doc block, neither start nor end on this line, can be removed safely
                $line->remove();
            }
        }

        $this->clearCache();
    }

    /**
     * Get the annotation content.
     */
    public function getContent(): string
    {
        return implode('', $this->lines);
    }

    public function supportTypes(): bool
    {
        return \in_array($this->getTag()->getName(), self::TAGS_WITH_TYPES, true);
    }

    /**
     * Get the current types content.
     *
     * Be careful modifying the underlying line as that won't flush the cache.
     */
    private function getTypesContent(): ?string
    {
        if (null === $this->typesContent) {
            $name = $this->getTag()->getName();

            if (!$this->supportTypes()) {
                throw new \RuntimeException('This tag does not support types.');
            }

            if (Preg::match(
                '{^(?:\h*\*|/\*\*)[\h*]*@'.$name.'\h+'.TypeExpression::REGEX_TYPES.'(?:(?:[*\h\v]|\&?[\.\$\s]).*)?\r?$}is',
                $this->getContent(),
                $matches,
            )) {
                \assert(isset($matches['types']));
                $this->typesContent = $matches['types'];
            }
        }

        return $this->typesContent;
    }

    private function clearCache(): void
    {
        $this->types = null;
        $this->typesContent = null;
    }
}
