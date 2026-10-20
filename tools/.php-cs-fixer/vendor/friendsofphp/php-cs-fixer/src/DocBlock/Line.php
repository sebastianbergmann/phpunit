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

/**
 * This represents a line of a docblock.
 *
 * @author Graham Campbell <hello@gjcampbell.co.uk>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class Line implements \Stringable
{
    /**
     * The content of this line.
     */
    private string $content;

    /**
     * Create a new line instance.
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * Get the string representation of object.
     */
    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * Get the content of this line.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Does this line contain useful content?
     *
     * If the line contains text or tags, then this is true.
     */
    public function containsUsefulContent(): bool
    {
        return Preg::match('/\*\s*\S+/', $this->content) && '' !== trim(str_replace(['/', '*'], ' ', $this->content));
    }

    /**
     * Does the line contain a tag?
     *
     * If this is true, then it must be the first line of an annotation.
     */
    public function containsATag(): bool
    {
        return Preg::match('/\*\s*@/', $this->content);
    }

    /**
     * Is the line the start of a docblock?
     */
    public function isTheStart(): bool
    {
        return str_contains($this->content, '/**');
    }

    /**
     * Is the line the end of a docblock?
     */
    public function isTheEnd(): bool
    {
        return str_contains($this->content, '*/');
    }

    /**
     * Set the content of this line.
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * Remove this line by clearing its contents.
     *
     * Note that this method technically brakes the internal state of the
     * docblock, but is useful when we need to retain the indices of lines
     * during the execution of an algorithm.
     */
    public function remove(): void
    {
        $this->content = '';
    }

    /**
     * Append a blank docblock line to this line's contents.
     *
     * Note that this method technically brakes the internal state of the
     * docblock, but is useful when we need to retain the indices of lines
     * during the execution of an algorithm.
     */
    public function addBlank(): void
    {
        $matched = Preg::match('/^(\h*\*)[^\r\n]*(\r?\n)$/', $this->content, $matches);

        if (!$matched) {
            return;
        }

        $this->content .= $matches[1].$matches[2];
    }
}
