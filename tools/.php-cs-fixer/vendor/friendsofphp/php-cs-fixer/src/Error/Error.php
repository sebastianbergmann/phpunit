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

namespace PhpCsFixer\Error;

/**
 * An abstraction for errors that can occur before and during fixing.
 *
 * @author Andreas Möller <am@localheinz.com>
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class Error implements \JsonSerializable
{
    /**
     * Error which has occurred in linting phase, before applying any fixers.
     */
    public const TYPE_INVALID = 1;

    /**
     * Error which has occurred during fixing phase.
     */
    public const TYPE_EXCEPTION = 2;

    /**
     * Error which has occurred in linting phase, after applying any fixers.
     */
    public const TYPE_LINT = 3;

    /** @var self::TYPE_* */
    private int $type;

    private string $filePath;

    private ?\Throwable $source;

    /**
     * @var list<string>
     */
    private array $appliedFixers;

    private string $diff;

    /**
     * @param self::TYPE_* $type
     * @param list<string> $appliedFixers
     */
    public function __construct(int $type, string $filePath, ?\Throwable $source = null, array $appliedFixers = [], string $diff = '')
    {
        $this->type = $type;
        $this->filePath = $filePath;
        $this->source = $source;
        $this->appliedFixers = $appliedFixers;
        $this->diff = $diff;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getSource(): ?\Throwable
    {
        return $this->source;
    }

    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return list<string>
     */
    public function getAppliedFixers(): array
    {
        return $this->appliedFixers;
    }

    public function getDiff(): string
    {
        return $this->diff;
    }

    /**
     * @return array{
     *     type: self::TYPE_*,
     *     filePath: string,
     *     source: null|array{class: class-string, message: string, code: int, file: string, line: int},
     *     appliedFixers: list<string>,
     *     diff: null|string
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'filePath' => $this->filePath,
            'source' => null !== $this->source
                ? [
                    'class' => \get_class($this->source),
                    'message' => $this->source->getMessage(),
                    'code' => $this->source->getCode(),
                    'file' => $this->source->getFile(),
                    'line' => $this->source->getLine(),
                ]
                : null,
            'appliedFixers' => $this->appliedFixers,
            'diff' => $this->diff,
        ];
    }
}
