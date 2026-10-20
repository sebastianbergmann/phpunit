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

namespace PhpCsFixer\Fixer\Comment;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class NoEmptyCommentFixer extends AbstractFixer
{
    private const TYPE_HASH = 1;

    private const TYPE_DOUBLE_SLASH = 2;

    private const TYPE_SLASH_ASTERISK = 3;

    /**
     * {@inheritdoc}
     *
     * Must run before NoExtraBlankLinesFixer, NoTrailingWhitespaceFixer, NoWhitespaceInBlankLineFixer, NoWhitespaceInEmptyArrayFixer.
     * Must run after PhpdocToCommentFixer.
     */
    public function getPriority(): int
    {
        return 2;
    }

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'There should not be any empty comments.',
            [new CodeSample("<?php\n//\n#\n/* */\n")],
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(\T_COMMENT);
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 1, $count = \count($tokens); $index < $count; ++$index) {
            if (!$tokens[$index]->isGivenKind(\T_COMMENT)) {
                continue;
            }

            $blockInfo = $this->getCommentBlock($tokens, $index);
            $blockStart = $blockInfo['blockStart'];
            $index = $blockInfo['blockEnd'];
            $isEmpty = $blockInfo['isEmpty'];

            if (false === $isEmpty) {
                continue;
            }

            for ($i = $blockStart; $i <= $index; ++$i) {
                $tokens->clearTokenAndMergeSurroundingWhitespace($i);
            }
        }
    }

    /**
     * Return the start index, end index and a flag stating if the comment block is empty.
     *
     * @param int $index T_COMMENT index
     *
     * @return array{blockStart: int, blockEnd: int, isEmpty: bool}
     */
    private function getCommentBlock(Tokens $tokens, int $index): array
    {
        $commentType = $this->getCommentType($tokens[$index]->getContent());
        $empty = $this->isEmptyComment($tokens[$index]->getContent());

        if (self::TYPE_SLASH_ASTERISK === $commentType) {
            return [
                'blockStart' => $index,
                'blockEnd' => $index,
                'isEmpty' => $empty,
            ];
        }

        $start = $index;
        $count = \count($tokens);
        ++$index;

        for (; $index < $count; ++$index) {
            if ($tokens[$index]->isComment()) {
                if ($commentType !== $this->getCommentType($tokens[$index]->getContent())) {
                    break;
                }

                if ($empty) { // don't retest if already known the block not being empty
                    $empty = $this->isEmptyComment($tokens[$index]->getContent());
                }

                continue;
            }

            if (!$tokens[$index]->isWhitespace() || $this->getLineBreakCount($tokens, $index, $index + 1) > 1) {
                break;
            }
        }

        return [
            'blockStart' => $start,
            'blockEnd' => $index - 1,
            'isEmpty' => $empty,
        ];
    }

    private function getCommentType(string $content): int
    {
        if (str_starts_with($content, '#')) {
            return self::TYPE_HASH;
        }

        if ('*' === $content[1]) {
            return self::TYPE_SLASH_ASTERISK;
        }

        return self::TYPE_DOUBLE_SLASH;
    }

    private function getLineBreakCount(Tokens $tokens, int $whiteStart, int $whiteEnd): int
    {
        $lineCount = 0;
        for ($i = $whiteStart; $i < $whiteEnd; ++$i) {
            $lineCount += Preg::matchAll('/\R/u', $tokens[$i]->getContent());
        }

        return $lineCount;
    }

    private function isEmptyComment(string $content): bool
    {
        $type = $this->getCommentType($content);
        \assert(\in_array($type, [self::TYPE_HASH, self::TYPE_SLASH_ASTERISK, self::TYPE_DOUBLE_SLASH], true));

        return Preg::match([
            self::TYPE_HASH => '|^#\s*$|', // single line comment starting with '#'
            self::TYPE_SLASH_ASTERISK => '|^/\*[\s\*]*\*+/$|', // comment starting with '/*' and ending with '*/' (but not a PHPDoc)
            self::TYPE_DOUBLE_SLASH => '|^//\s*$|', // single line comment starting with '//'
        ][$type], $content);
    }
}
