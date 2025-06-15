<?php declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer: custom fixers.
 *
 * (c) 2018 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace PhpCsFixerCustomFixers\Fixer;

use PhpCsFixer\Fixer\Basic\NumericLiteralSeparatorFixer as NLSFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\DeprecatedFixerInterface;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Preg;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @deprecated
 *
 * @implements ConfigurableFixerInterface<_InputConfig, _Config>
 *
 * @phpstan-type _InputConfig array{binary?: bool, decimal?: bool, float?: bool, hexadecimal?: bool, octal?: bool}
 * @phpstan-type _Config array{binary: bool, decimal: bool, float: bool, hexadecimal: bool, octal: bool}
 *
 * @no-named-arguments
 */
final class NumericLiteralSeparatorFixer extends AbstractFixer implements ConfigurableFixerInterface, DeprecatedFixerInterface
{
    private ?bool $binarySeparator = false;
    private ?bool $decimalSeparator = false;
    private ?bool $floatSeparator = false;
    private ?bool $hexadecimalSeparator = false;
    private ?bool $octalSeparator = false;

    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Numeric literals must have configured separators.',
            [new VersionSpecificCodeSample(
                '<?php
echo 0b01010100_01101000; // binary
echo 135_798_642; // decimal
echo 1_234.456_78e-4_321; // float
echo 0xAE_B0_42_FC; // hexadecimal
echo 0123_4567; // octal
',
                new VersionSpecification(70400),
            )],
            '',
        );
    }

    public function getConfigurationDefinition(): FixerConfigurationResolverInterface
    {
        return new FixerConfigurationResolver([
            (new FixerOptionBuilder('binary', 'whether add, remove or ignore separators in binary numbers.'))
                ->setAllowedTypes(['bool', 'null'])
                ->setDefault($this->binarySeparator)
                ->getOption(),
            (new FixerOptionBuilder('decimal', 'whether add, remove or ignore separators in decimal numbers.'))
                ->setAllowedTypes(['bool', 'null'])
                ->setDefault($this->decimalSeparator)
                ->getOption(),
            (new FixerOptionBuilder('float', 'whether add, remove or ignore separators in float numbers.'))
                ->setAllowedTypes(['bool', 'null'])
                ->setDefault($this->floatSeparator)
                ->getOption(),
            (new FixerOptionBuilder('hexadecimal', 'whether add, remove or ignore separators in hexadecimal numbers.'))
                ->setAllowedTypes(['bool', 'null'])
                ->setDefault($this->hexadecimalSeparator)
                ->getOption(),
            (new FixerOptionBuilder('octal', 'whether add, remove or ignore separators in octal numbers.'))
                ->setAllowedTypes(['bool', 'null'])
                ->setDefault($this->octalSeparator)
                ->getOption(),
        ]);
    }

    /**
     * @param array<string, null|bool> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->binarySeparator = \array_key_exists('binary', $configuration) ? $configuration['binary'] : $this->binarySeparator;
        $this->decimalSeparator = \array_key_exists('decimal', $configuration) ? $configuration['decimal'] : $this->decimalSeparator;
        $this->floatSeparator = \array_key_exists('float', $configuration) ? $configuration['float'] : $this->floatSeparator;
        $this->hexadecimalSeparator = \array_key_exists('hexadecimal', $configuration) ? $configuration['hexadecimal'] : $this->hexadecimalSeparator;
        $this->octalSeparator = \array_key_exists('octal', $configuration) ? $configuration['octal'] : $this->octalSeparator;
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isAnyTokenKindsFound([\T_DNUMBER, \T_LNUMBER]);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(\SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = $tokens->count() - 1; $index > 0; $index--) {
            if (!$tokens[$index]->isGivenKind([\T_DNUMBER, \T_LNUMBER])) {
                continue;
            }

            $content = $tokens[$index]->getContent();
            $newContent = $this->getNewContent($content);

            if ($content !== $newContent) {
                $id = $tokens[$index]->getId();
                \assert(\is_int($id));

                $tokens[$index] = new Token([$id, $newContent]);
            }
        }
    }

    /**
     * @return list<string>
     */
    public function getSuccessorsNames(): array
    {
        return [(new NLSFixer())->getName()];
    }

    private function getNewContent(string $content): string
    {
        if (\strpos($content, '.') !== false) {
            $content = $this->updateContent($content, null, '.', 3, $this->floatSeparator);
            $content = $this->updateContent($content, '.', 'e', 3, $this->floatSeparator, false);

            return $this->updateContent($content, 'e', null, 3, $this->floatSeparator);
        }

        if (\stripos($content, '0b') === 0) {
            return $this->updateContent($content, 'b', null, 8, $this->binarySeparator);
        }

        if (\stripos($content, '0x') === 0) {
            return $this->updateContent($content, 'x', null, 2, $this->hexadecimalSeparator);
        }

        if (Preg::match('/e-?[\\d_]+$/i', $content)) {
            $content = $this->updateContent($content, null, 'e', 3, $this->floatSeparator);

            return $this->updateContent($content, 'e', null, 3, $this->floatSeparator);
        }

        if (\strpos($content, '0') === 0) {
            return $this->updateContent($content, '0', null, 4, $this->octalSeparator);
        }

        return $this->updateContent($content, null, null, 3, $this->decimalSeparator);
    }

    private function updateContent(string $content, ?string $startCharacter, ?string $endCharacter, int $groupSize, ?bool $addSeparators, bool $fromRight = true): string
    {
        if ($addSeparators === null) {
            return $content;
        }

        $startPosition = $this->getStartPosition($content, $startCharacter);
        if ($startPosition === null) {
            return $content;
        }
        $endPosition = $this->getEndPosition($content, $endCharacter);

        $substringToUpdate = \substr($content, $startPosition, $endPosition - $startPosition);
        $substringToUpdate = \str_replace('_', '', $substringToUpdate);

        if ($addSeparators) {
            if ($fromRight) {
                $substringToUpdate = \strrev($substringToUpdate);
            }

            $substringToUpdate = Preg::replace(\sprintf('/[\\da-fA-F]{%d}(?!-)(?!$)/', $groupSize), '$0_', $substringToUpdate);

            if ($fromRight) {
                $substringToUpdate = \strrev($substringToUpdate);
            }
        }

        return \substr($content, 0, $startPosition) . $substringToUpdate . \substr($content, $endPosition);
    }

    private function getStartPosition(string $content, ?string $startCharacter): ?int
    {
        if ($startCharacter === null) {
            return 0;
        }

        $startPosition = \stripos($content, $startCharacter);

        if ($startPosition === false) {
            return null;
        }

        return $startPosition + 1;
    }

    private function getEndPosition(string $content, ?string $endCharacter): int
    {
        if ($endCharacter === null) {
            return \strlen($content);
        }

        $endPosition = \stripos($content, $endCharacter);

        if ($endPosition === false) {
            return \strlen($content);
        }

        return $endPosition;
    }
}
