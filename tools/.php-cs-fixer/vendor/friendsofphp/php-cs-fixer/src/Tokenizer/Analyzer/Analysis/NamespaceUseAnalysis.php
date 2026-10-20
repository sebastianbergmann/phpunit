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

namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;

/**
 * @readonly
 *
 * @TODO v4: previously was mareked as internal - yet it leaked to public interface of `DocBlock`, consider making it so again.
 *
 * @phpstan-type _ImportType 'class'|'constant'|'function'
 *
 * @author VeeWee <toonverwerft@gmail.com>
 * @author Greg Korba <greg@codito.dev>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NamespaceUseAnalysis
{
    public const TYPE_CLASS = 1; // "classy" could be class, interface or trait
    public const TYPE_FUNCTION = 2;
    public const TYPE_CONSTANT = 3;

    /**
     * The fully qualified use namespace.
     *
     * @var non-empty-string
     */
    private string $fullName;

    /**
     * The short version of use namespace or the alias name in case of aliased use statements.
     *
     * @var non-empty-string
     */
    private string $shortName;

    /**
     * Is the use statement part of multi-use (`use A, B, C;`, `use A\{B, C};`)?
     */
    private bool $isInMulti;

    /**
     * Is the use statement being aliased?
     */
    private bool $isAliased;

    /**
     * The start index of the namespace declaration in the analysed Tokens.
     */
    private int $startIndex;

    /**
     * The end index of the namespace declaration in the analysed Tokens.
     */
    private int $endIndex;

    /**
     * The start index of the single import in the multi-use statement.
     */
    private ?int $chunkStartIndex;

    /**
     * The end index of the single import in the multi-use statement.
     */
    private ?int $chunkEndIndex;

    /**
     * The type of import: class, function or constant.
     *
     * @var self::TYPE_*
     */
    private int $type;

    /**
     * @param self::TYPE_*     $type
     * @param non-empty-string $fullName
     * @param non-empty-string $shortName
     *
     * @internal
     */
    public function __construct(
        int $type,
        string $fullName,
        string $shortName,
        bool $isAliased,
        bool $isInMulti,
        int $startIndex,
        int $endIndex,
        ?int $chunkStartIndex = null,
        ?int $chunkEndIndex = null
    ) {
        if (true === $isInMulti && (null === $chunkStartIndex || null === $chunkEndIndex)) {
            throw new \LogicException('Chunk start and end index must be set when the import is part of a multi-use statement.');
        }

        $this->type = $type;
        $this->fullName = $fullName;
        $this->shortName = $shortName;
        $this->isAliased = $isAliased;
        $this->isInMulti = $isInMulti;
        $this->startIndex = $startIndex;
        $this->endIndex = $endIndex;
        $this->chunkStartIndex = $chunkStartIndex;
        $this->chunkEndIndex = $chunkEndIndex;
    }

    /**
     * @return non-empty-string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @return non-empty-string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function isAliased(): bool
    {
        return $this->isAliased;
    }

    public function isInMulti(): bool
    {
        return $this->isInMulti;
    }

    public function getStartIndex(): int
    {
        return $this->startIndex;
    }

    public function getEndIndex(): int
    {
        return $this->endIndex;
    }

    public function getChunkStartIndex(): ?int
    {
        return $this->chunkStartIndex;
    }

    public function getChunkEndIndex(): ?int
    {
        return $this->chunkEndIndex;
    }

    /**
     * @return self::TYPE_*
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return _ImportType
     */
    public function getHumanFriendlyType(): string
    {
        return [
            self::TYPE_CLASS => 'class',
            self::TYPE_FUNCTION => 'function',
            self::TYPE_CONSTANT => 'constant',
        ][$this->type];
    }

    public function isClass(): bool
    {
        return self::TYPE_CLASS === $this->type;
    }

    public function isFunction(): bool
    {
        return self::TYPE_FUNCTION === $this->type;
    }

    public function isConstant(): bool
    {
        return self::TYPE_CONSTANT === $this->type;
    }
}
