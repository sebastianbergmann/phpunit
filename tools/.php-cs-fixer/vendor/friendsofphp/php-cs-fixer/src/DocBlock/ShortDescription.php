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

/**
 * This class represents a short description (aka summary) of a docblock.
 *
 * @readonly
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ShortDescription
{
    /**
     * The docblock containing the short description.
     */
    private DocBlock $doc;

    public function __construct(DocBlock $doc)
    {
        $this->doc = $doc;
    }

    /**
     * Get the line index of the line containing the end of the short
     * description, if present.
     */
    public function getEnd(): ?int
    {
        $reachedContent = false;

        foreach ($this->doc->getLines() as $index => $line) {
            // we went past a description, then hit a tag or blank line, so
            // the last line of the description must be the one before this one
            if ($reachedContent && ($line->containsATag() || !$line->containsUsefulContent())) {
                return $index - 1;
            }

            // no short description was found
            if ($line->containsATag()) {
                return null;
            }

            // we've reached content, but need to check the next lines too
            // in case the short description is multi-line
            if ($line->containsUsefulContent()) {
                $reachedContent = true;
            }
        }

        return null;
    }
}
