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

use PhpCsFixer\Tokenizer\Analyzer\AttributeAnalyzer;
use PhpCsFixer\Tokenizer\Analyzer\GotoLabelAnalyzer;

/**
 * Analyzer of Tokens collection.
 *
 * Its role is to provide the ability to analyse collection.
 *
 * @internal
 *
 * @phpstan-type _ClassyElementType 'case'|'const'|'method'|'property'|'promoted_property'|'trait_import'
 *
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 * @author Gregor Harlan <gharlan@web.de>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class TokensAnalyzer
{
    /**
     * Tokens collection instance.
     */
    private Tokens $tokens;

    /**
     * @readonly
     */
    private GotoLabelAnalyzer $gotoLabelAnalyzer;

    public function __construct(Tokens $tokens)
    {
        $this->tokens = $tokens;
        $this->gotoLabelAnalyzer = new GotoLabelAnalyzer();
    }

    /**
     * Get indices of methods and properties in classy code (classes, interfaces and traits).
     *
     * @return array<int, array{classIndex: int, token: Token, type: _ClassyElementType}>
     */
    public function getClassyElements(): array
    {
        $elements = [];

        for ($index = 1, $count = \count($this->tokens) - 2; $index < $count; ++$index) {
            if ($this->tokens[$index]->isClassy()) {
                [$index, $newElements] = $this->findClassyElements($index, $index);
                $elements += $newElements;
            }
        }

        ksort($elements);

        return $elements;
    }

    /**
     * Get indices of modifiers of a classy code (classes, interfaces and traits).
     *
     * @return array{
     *     final: null|int,
     *     abstract: null|int,
     *     readonly: null|int
     * }
     */
    public function getClassyModifiers(int $index): array
    {
        if (!$this->tokens[$index]->isClassy()) {
            throw new \InvalidArgumentException(\sprintf('Not an "classy" at given index %d.', $index));
        }

        $modifiers = ['final' => null, 'abstract' => null, 'readonly' => null];

        while (true) {
            $index = $this->tokens->getPrevMeaningfulToken($index);

            if ($this->tokens[$index]->isGivenKind(\T_FINAL)) {
                $modifiers['final'] = $index;
            } elseif ($this->tokens[$index]->isGivenKind(\T_ABSTRACT)) {
                $modifiers['abstract'] = $index;
            } elseif ($this->tokens[$index]->isGivenKind(FCT::T_READONLY)) {
                $modifiers['readonly'] = $index;
            } else { // no need to skip attributes as it is not possible on PHP8.2
                break;
            }
        }

        return $modifiers;
    }

    /**
     * Get indices of namespace uses.
     *
     * @param bool $perNamespace Return namespace uses per namespace
     *
     * @return ($perNamespace is true ? array<int, non-empty-list<int>> : list<int>)
     */
    public function getImportUseIndexes(bool $perNamespace = false): array
    {
        $tokens = $this->tokens;

        $uses = [];
        $namespaceIndex = 0;

        for ($index = 0, $limit = $tokens->count(); $index < $limit; ++$index) {
            $token = $tokens[$index];

            if ($token->isGivenKind(\T_NAMESPACE)) {
                $nextTokenIndex = $tokens->getNextTokenOfKind($index, [';', '{']);
                $nextToken = $tokens[$nextTokenIndex];

                if ($nextToken->equals('{')) {
                    $index = $nextTokenIndex;
                }

                if ($perNamespace) {
                    ++$namespaceIndex;
                }

                continue;
            }

            if ($token->isGivenKind(\T_USE)) {
                $uses[$namespaceIndex][] = $index;
            }
        }

        if (!$perNamespace && isset($uses[$namespaceIndex])) {
            return $uses[$namespaceIndex];
        }

        return $uses;
    }

    /**
     * Check if there is an array at given index.
     */
    public function isArray(int $index): bool
    {
        return $this->tokens[$index]->isGivenKind([\T_ARRAY, CT::T_ARRAY_BRACKET_OPEN]);
    }

    /**
     * Check if the array at index is multiline.
     *
     * This only checks the root-level of the array.
     */
    public function isArrayMultiLine(int $index): bool
    {
        if (!$this->isArray($index)) {
            throw new \InvalidArgumentException(\sprintf('Not an array at given index %d.', $index));
        }

        $tokens = $this->tokens;

        // Skip only when it's an array, for short arrays we need the brace for correct
        // level counting
        if ($tokens[$index]->isGivenKind(\T_ARRAY)) {
            $index = $tokens->getNextMeaningfulToken($index);
        }

        return $this->isBlockMultiline($tokens, $index);
    }

    public function isBlockMultiline(Tokens $tokens, int $index): bool
    {
        $blockType = Tokens::detectBlockType($tokens[$index]);

        if (null === $blockType || !$blockType['isStart']) {
            throw new \InvalidArgumentException(\sprintf('Not an block start at given index %d.', $index));
        }

        $endIndex = $tokens->findBlockEnd($blockType['type'], $index);

        for (++$index; $index < $endIndex; ++$index) {
            $token = $tokens[$index];
            $blockType = Tokens::detectBlockType($token);

            if (null !== $blockType && $blockType['isStart']) {
                $index = $tokens->findBlockEnd($blockType['type'], $index);

                continue;
            }

            if (
                $token->isWhitespace()
                && !$tokens[$index - 1]->isGivenKind(\T_END_HEREDOC)
                && str_contains($token->getContent(), "\n")
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $index Index of the T_FUNCTION token
     *
     * @return array{visibility: null|T_PRIVATE|T_PROTECTED|T_PUBLIC, static: bool, abstract: bool, final: bool}
     */
    public function getMethodAttributes(int $index): array
    {
        if (!$this->tokens[$index]->isGivenKind(\T_FUNCTION)) {
            throw new \LogicException(\sprintf('No T_FUNCTION at given index %d, got "%s".', $index, $this->tokens[$index]->getName()));
        }

        $attributes = [
            'visibility' => null,
            'static' => false,
            'abstract' => false,
            'final' => false,
        ];

        for ($i = $index; $i >= 0; --$i) {
            $i = $this->tokens->getPrevMeaningfulToken($i);
            $token = $this->tokens[$i];

            if ($token->isGivenKind(\T_STATIC)) {
                $attributes['static'] = true;

                continue;
            }

            if ($token->isGivenKind(\T_FINAL)) {
                $attributes['final'] = true;

                continue;
            }

            if ($token->isGivenKind(\T_ABSTRACT)) {
                $attributes['abstract'] = true;

                continue;
            }

            // visibility

            if ($token->isGivenKind(\T_PRIVATE)) {
                $attributes['visibility'] = \T_PRIVATE;

                continue;
            }

            if ($token->isGivenKind(\T_PROTECTED)) {
                $attributes['visibility'] = \T_PROTECTED;

                continue;
            }

            if ($token->isGivenKind(\T_PUBLIC)) {
                $attributes['visibility'] = \T_PUBLIC;

                continue;
            }

            // found a meaningful token that is not part of
            // the function signature; stop looking
            break;
        }

        return $attributes;
    }

    /**
     * Check if there is an anonymous class under given index.
     */
    public function isAnonymousClass(int $index): bool
    {
        if (!$this->tokens[$index]->isClassy()) {
            throw new \LogicException(\sprintf('No classy token at given index %d.', $index));
        }

        if (!$this->tokens[$index]->isGivenKind(\T_CLASS)) {
            return false;
        }

        $index = $this->tokens->getPrevMeaningfulToken($index);

        if ($this->tokens[$index]->isGivenKind(FCT::T_READONLY)) {
            $index = $this->tokens->getPrevMeaningfulToken($index);
        }

        while ($this->tokens[$index]->isGivenKind(CT::T_ATTRIBUTE_CLOSE)) {
            $index = $this->tokens->findBlockStart(Tokens::BLOCK_TYPE_ATTRIBUTE, $index);
            $index = $this->tokens->getPrevMeaningfulToken($index);
        }

        return $this->tokens[$index]->isGivenKind(\T_NEW);
    }

    /**
     * Check if the function under given index is a lambda.
     */
    public function isLambda(int $index): bool
    {
        if (!$this->tokens[$index]->isGivenKind([\T_FUNCTION, \T_FN])) {
            throw new \LogicException(\sprintf('No T_FUNCTION or T_FN at given index %d, got "%s".', $index, $this->tokens[$index]->getName()));
        }

        $startParenthesisIndex = $this->tokens->getNextMeaningfulToken($index);
        $startParenthesisToken = $this->tokens[$startParenthesisIndex];

        // skip & for `function & () {}` syntax
        if ($startParenthesisToken->isGivenKind(CT::T_RETURN_REF)) {
            $startParenthesisIndex = $this->tokens->getNextMeaningfulToken($startParenthesisIndex);
            $startParenthesisToken = $this->tokens[$startParenthesisIndex];
        }

        return $startParenthesisToken->equals('(');
    }

    public function getLastTokenIndexOfArrowFunction(int $index): int
    {
        if (!$this->tokens[$index]->isGivenKind(\T_FN)) {
            throw new \InvalidArgumentException(\sprintf('Not an "arrow function" at given index %d.', $index));
        }

        $stopTokens = [')', ']', ',', ';', [\T_CLOSE_TAG]];
        $index = $this->tokens->getNextTokenOfKind($index, [[\T_DOUBLE_ARROW]]);

        while (true) {
            $index = $this->tokens->getNextMeaningfulToken($index);

            if ($this->tokens[$index]->equalsAny($stopTokens)) {
                break;
            }

            $blockType = Tokens::detectBlockType($this->tokens[$index]);

            if (null === $blockType) {
                continue;
            }

            if ($blockType['isStart']) {
                $index = $this->tokens->findBlockEnd($blockType['type'], $index);

                continue;
            }

            break;
        }

        return $this->tokens->getPrevMeaningfulToken($index);
    }

    /**
     * Check if the T_STRING under given index is a constant invocation.
     */
    public function isConstantInvocation(int $index): bool
    {
        if (!$this->tokens[$index]->isGivenKind(\T_STRING)) {
            throw new \LogicException(\sprintf('No T_STRING at given index %d, got "%s".', $index, $this->tokens[$index]->getName()));
        }

        $nextIndex = $this->tokens->getNextMeaningfulToken($index);

        if (
            $this->tokens[$nextIndex]->equalsAny(['(', '{'])
            || $this->tokens[$nextIndex]->isGivenKind([\T_DOUBLE_COLON, \T_ELLIPSIS, \T_NS_SEPARATOR, CT::T_RETURN_REF, CT::T_TYPE_ALTERNATION, CT::T_TYPE_INTERSECTION, \T_VARIABLE])
        ) {
            return false;
        }

        // handle foreach( FOO as $_ ) {}
        if ($this->tokens[$nextIndex]->isGivenKind(\T_AS)) {
            $prevIndex = $this->tokens->getPrevMeaningfulToken($index);

            if (!$this->tokens[$prevIndex]->equals('(')) {
                return false;
            }
        }

        $prevIndex = $this->tokens->getPrevMeaningfulToken($index);

        if ($this->tokens[$prevIndex]->isGivenKind(Token::getClassyTokenKinds())) {
            return false;
        }

        if ($this->tokens[$prevIndex]->isGivenKind([\T_AS, \T_CONST, \T_DOUBLE_COLON, \T_FUNCTION, \T_GOTO, CT::T_GROUP_IMPORT_BRACE_OPEN, CT::T_TYPE_COLON, CT::T_TYPE_ALTERNATION, CT::T_TYPE_INTERSECTION]) || $this->tokens[$prevIndex]->isObjectOperator()) {
            return false;
        }

        if (
            $this->tokens[$prevIndex]->isGivenKind(\T_CASE)
            && $this->tokens->isAllTokenKindsFound([FCT::T_ENUM])
        ) {
            $enumSwitchIndex = $this->tokens->getPrevTokenOfKind($index, [[\T_SWITCH], [\T_ENUM]]);

            if (!$this->tokens[$enumSwitchIndex]->isGivenKind(\T_SWITCH)) {
                return false;
            }
        }

        while ($this->tokens[$prevIndex]->isGivenKind([CT::T_NAMESPACE_OPERATOR, \T_NS_SEPARATOR, \T_STRING, CT::T_ARRAY_TYPEHINT])) {
            $prevIndex = $this->tokens->getPrevMeaningfulToken($prevIndex);
        }

        if ($this->tokens[$prevIndex]->isGivenKind([CT::T_CONST_IMPORT, \T_EXTENDS, CT::T_FUNCTION_IMPORT, \T_IMPLEMENTS, \T_INSTANCEOF, \T_INSTEADOF, \T_NAMESPACE, \T_NEW, CT::T_NULLABLE_TYPE, CT::T_TYPE_COLON, \T_USE, CT::T_USE_TRAIT, CT::T_TYPE_INTERSECTION, CT::T_TYPE_ALTERNATION, \T_CONST, CT::T_DISJUNCTIVE_NORMAL_FORM_TYPE_PARENTHESIS_CLOSE])) {
            return false;
        }

        // `FOO & $bar` could be:
        //   - function reference parameter: function baz(Foo & $bar) {}
        //   - bit operator: $x = FOO & $bar;
        if ($this->tokens[$nextIndex]->equals('&') && $this->tokens[$this->tokens->getNextMeaningfulToken($nextIndex)]->isGivenKind(\T_VARIABLE)) {
            $checkIndex = $this->tokens->getPrevTokenOfKind($prevIndex, [';', '{', '}', [\T_FUNCTION], [\T_OPEN_TAG], [\T_OPEN_TAG_WITH_ECHO]]);

            if ($this->tokens[$checkIndex]->isGivenKind(\T_FUNCTION)) {
                return false;
            }
        }

        // check for `extends`/`implements`/`use` list
        if ($this->tokens[$prevIndex]->equals(',')) {
            $checkIndex = $prevIndex;

            while ($this->tokens[$checkIndex]->equalsAny([',', [\T_AS], [CT::T_NAMESPACE_OPERATOR], [\T_NS_SEPARATOR], [\T_STRING]])) {
                $checkIndex = $this->tokens->getPrevMeaningfulToken($checkIndex);
            }

            if ($this->tokens[$checkIndex]->isGivenKind([\T_EXTENDS, CT::T_GROUP_IMPORT_BRACE_OPEN, \T_IMPLEMENTS, \T_USE, CT::T_USE_TRAIT])) {
                return false;
            }
        }

        // check for array in double quoted string: `"..$foo[bar].."`
        if ($this->tokens[$prevIndex]->equals('[') && $this->tokens[$nextIndex]->equals(']')) {
            $checkToken = $this->tokens[$this->tokens->getNextMeaningfulToken($nextIndex)];

            if ($checkToken->equals('"') || $checkToken->isGivenKind([\T_CURLY_OPEN, \T_DOLLAR_OPEN_CURLY_BRACES, \T_ENCAPSED_AND_WHITESPACE, \T_VARIABLE])) {
                return false;
            }
        }

        // check for attribute: `#[Foo]`
        if (AttributeAnalyzer::isAttribute($this->tokens, $index)) {
            return false;
        }

        // check for goto label
        if ($this->tokens[$nextIndex]->equals(':')) {
            if ($this->gotoLabelAnalyzer->belongsToGoToLabel($this->tokens, $nextIndex)) {
                return false;
            }
        }

        // check for non-capturing catches

        while ($this->tokens[$prevIndex]->isGivenKind([CT::T_NAMESPACE_OPERATOR, \T_NS_SEPARATOR, \T_STRING, CT::T_TYPE_ALTERNATION])) {
            $prevIndex = $this->tokens->getPrevMeaningfulToken($prevIndex);
        }

        if ($this->tokens[$prevIndex]->equals('(')) {
            $prevPrevIndex = $this->tokens->getPrevMeaningfulToken($prevIndex);

            if ($this->tokens[$prevPrevIndex]->isGivenKind(\T_CATCH)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if there is a unary successor operator under given index.
     */
    public function isUnarySuccessorOperator(int $index): bool
    {
        $tokens = $this->tokens;
        $token = $tokens[$index];

        if (!$token->isGivenKind([\T_INC, \T_DEC])) {
            return false;
        }

        $prevToken = $tokens[$tokens->getPrevMeaningfulToken($index)];

        return $prevToken->equalsAny([
            ']',
            [\T_STRING],
            [\T_VARIABLE],
            [CT::T_ARRAY_INDEX_BRACE_CLOSE],
            [CT::T_DYNAMIC_PROP_BRACE_CLOSE],
            [CT::T_DYNAMIC_VAR_BRACE_CLOSE],
        ]);
    }

    /**
     * Checks if there is a unary predecessor operator under given index.
     */
    public function isUnaryPredecessorOperator(int $index): bool
    {
        $tokens = $this->tokens;
        $token = $tokens[$index];

        // potential unary successor operator
        if ($token->isGivenKind([\T_INC, \T_DEC])) {
            return !$this->isUnarySuccessorOperator($index);
        }

        // always unary predecessor operator
        if ($token->equalsAny(['!', '~', '@', [\T_ELLIPSIS]])) {
            return true;
        }

        // potential binary operator
        if (!$token->equalsAny(['+', '-', '&', [CT::T_RETURN_REF]])) {
            return false;
        }

        $prevToken = $tokens[$tokens->getPrevMeaningfulToken($index)];

        if (!$prevToken->equalsAny([
            ']',
            '}',
            ')',
            '"',
            '`',
            [CT::T_ARRAY_BRACKET_CLOSE],
            [CT::T_ARRAY_INDEX_BRACE_CLOSE],
            [CT::T_DYNAMIC_PROP_BRACE_CLOSE],
            [CT::T_DYNAMIC_VAR_BRACE_CLOSE],
            [\T_CLASS_C],
            [\T_CONSTANT_ENCAPSED_STRING],
            [\T_DEC],
            [\T_DIR],
            [\T_DNUMBER],
            [\T_FILE],
            [\T_FUNC_C],
            [\T_INC],
            [\T_LINE],
            [\T_LNUMBER],
            [\T_METHOD_C],
            [\T_NS_C],
            [\T_STRING],
            [\T_TRAIT_C],
            [\T_VARIABLE],
        ])) {
            return true;
        }

        if (!$token->equals('&') || !$prevToken->isGivenKind(\T_STRING)) {
            return false;
        }

        $prevToken = $tokens[$tokens->getPrevTokenOfKind($index, [
            ';',
            '{',
            '}',
            [\T_DOUBLE_ARROW],
            [\T_FN],
            [\T_FUNCTION],
            [\T_OPEN_TAG],
            [\T_OPEN_TAG_WITH_ECHO],
        ])];

        return $prevToken->isGivenKind([\T_FN, \T_FUNCTION]);
    }

    /**
     * Checks if there is a binary operator under given index.
     */
    public function isBinaryOperator(int $index): bool
    {
        $tokens = $this->tokens;
        $token = $tokens[$index];

        if ($token->isGivenKind([\T_INLINE_HTML, \T_ENCAPSED_AND_WHITESPACE, CT::T_TYPE_INTERSECTION])) {
            return false;
        }

        // potential unary predecessor operator
        if (\in_array($token->getContent(), ['+', '-', '&'], true)) {
            return !$this->isUnaryPredecessorOperator($index);
        }

        if ($token->isArray()) {
            return \in_array($token->getId(), [
                \T_AND_EQUAL,            // &=
                \T_BOOLEAN_AND,          // &&
                \T_BOOLEAN_OR,           // ||
                \T_CONCAT_EQUAL,         // .=
                \T_DIV_EQUAL,            // /=
                \T_DOUBLE_ARROW,         // =>
                \T_IS_EQUAL,             // ==
                \T_IS_GREATER_OR_EQUAL,  // >=
                \T_IS_IDENTICAL,         // ===
                \T_IS_NOT_EQUAL,         // !=, <>
                \T_IS_NOT_IDENTICAL,     // !==
                \T_IS_SMALLER_OR_EQUAL,  // <=
                \T_LOGICAL_AND,          // and
                \T_LOGICAL_OR,           // or
                \T_LOGICAL_XOR,          // xor
                \T_MINUS_EQUAL,          // -=
                \T_MOD_EQUAL,            // %=
                \T_MUL_EQUAL,            // *=
                \T_OR_EQUAL,             // |=
                \T_PLUS_EQUAL,           // +=
                \T_POW,                  // **
                \T_POW_EQUAL,            // **=
                \T_SL,                   // <<
                \T_SL_EQUAL,             // <<=
                \T_SR,                   // >>
                \T_SR_EQUAL,             // >>=
                \T_XOR_EQUAL,            // ^=
                \T_SPACESHIP,            // <=>
                \T_COALESCE,             // ??
                \T_COALESCE_EQUAL,       // ??=
            ], true);
        }

        if (\in_array($token->getContent(), ['=', '*', '/', '%', '<', '>', '|', '^', '.'], true)) {
            return true;
        }

        return false;
    }

    /**
     * Check if `T_WHILE` token at given index is `do { ... } while ();` syntax
     * and not `while () { ...}`.
     */
    public function isWhilePartOfDoWhile(int $index): bool
    {
        $tokens = $this->tokens;
        $token = $tokens[$index];

        if (!$token->isGivenKind(\T_WHILE)) {
            throw new \LogicException(\sprintf('No T_WHILE at given index %d, got "%s".', $index, $token->getName()));
        }

        $endIndex = $tokens->getPrevMeaningfulToken($index);
        if (!$tokens[$endIndex]->equals('}')) {
            return false;
        }

        $startIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_BRACE, $endIndex);
        $beforeStartIndex = $tokens->getPrevMeaningfulToken($startIndex);

        return $tokens[$beforeStartIndex]->isGivenKind(\T_DO);
    }

    /**
     * @throws \LogicException when provided index does not point to token containing T_CASE
     */
    public function isEnumCase(int $caseIndex): bool
    {
        $tokens = $this->tokens;
        $token = $tokens[$caseIndex];

        if (!$token->isGivenKind(\T_CASE)) {
            throw new \LogicException(\sprintf(
                'No T_CASE given at index %d, got %s instead.',
                $caseIndex,
                $token->getName() ?? $token->getContent(),
            ));
        }

        if (!$tokens->isTokenKindFound(FCT::T_ENUM)) {
            return false;
        }

        $prevIndex = $caseIndex;

        // get the T_ENUM or T_SWITCH that is matching the T_CASE, detecting and skipping the {...} blocks in between, as they may have nested switch-case
        while (true) {
            $prevIndex = $tokens->getPrevTokenOfKind($prevIndex, ['}', [\T_ENUM], [\T_SWITCH]]);
            \assert(null !== $prevIndex);

            if ($tokens[$prevIndex]->equals('}')) {
                $prevIndex = $tokens->findBlockStart(Tokens::BLOCK_TYPE_BRACE, $prevIndex);
            } else {
                break;
            }
        }

        return $tokens[$prevIndex]->isGivenKind(\T_ENUM);
    }

    public function isSuperGlobal(int $index): bool
    {
        $token = $this->tokens[$index];

        if (!$token->isGivenKind(\T_VARIABLE)) {
            return false;
        }

        return \in_array(strtoupper($token->getContent()), [
            '$_COOKIE',
            '$_ENV',
            '$_FILES',
            '$_GET',
            '$_POST',
            '$_REQUEST',
            '$_SERVER',
            '$_SESSION',
            '$GLOBALS',
        ], true);
    }

    /**
     * Find classy elements.
     *
     * Searches in tokens from the classy (start) index till the end (index) of the classy.
     * Returns an array; first value is the index until the method has analysed (int), second the found classy elements (array).
     *
     * @param int $classIndex classy index
     *
     * @return array{int, array<int, array{classIndex: int, token: Token, type: _ClassyElementType}>}
     */
    private function findClassyElements(int $classIndex, int $index): array
    {
        $elements = [];
        $curlyBracesLevel = 0;
        $bracesLevel = 0;
        ++$index; // skip the classy index itself

        for ($count = \count($this->tokens); $index < $count; ++$index) {
            $token = $this->tokens[$index];

            if ($token->isGivenKind(\T_ENCAPSED_AND_WHITESPACE)) {
                continue;
            }

            if ($token->isGivenKind(\T_CLASS)) { // anonymous class in class
                // check for nested anonymous classes inside the new call of an anonymous class,
                // for example `new class(function (){new class(function (){new class(function (){}){};}){};}){};` etc.
                // if class(XYZ) {} skip till `(` as XYZ might contain functions etc.

                $nestedClassIndex = $index;
                $index = $this->tokens->getNextMeaningfulToken($index);

                if ($this->tokens[$index]->equals('(')) {
                    ++$index; // move after `(`

                    for ($nestedBracesLevel = 1; $index < $count; ++$index) {
                        $token = $this->tokens[$index];

                        if ($token->equals('(')) {
                            ++$nestedBracesLevel;

                            continue;
                        }

                        if ($token->equals(')')) {
                            --$nestedBracesLevel;

                            if (0 === $nestedBracesLevel) {
                                [$index, $newElements] = $this->findClassyElements($nestedClassIndex, $index);
                                $elements += $newElements;

                                break;
                            }

                            continue;
                        }

                        if ($token->isGivenKind(\T_CLASS)) { // anonymous class in class
                            [$index, $newElements] = $this->findClassyElements($index, $index);
                            $elements += $newElements;
                        }
                    }
                } else {
                    [$index, $newElements] = $this->findClassyElements($nestedClassIndex, $nestedClassIndex);
                    $elements += $newElements;
                }

                continue;
            }

            if ($token->equals('(')) {
                ++$bracesLevel;

                continue;
            }

            if ($token->equals(')')) {
                --$bracesLevel;

                continue;
            }

            if ($token->equals('{')) {
                ++$curlyBracesLevel;

                continue;
            }

            if ($token->equals('}')) {
                --$curlyBracesLevel;

                if (0 === $curlyBracesLevel) {
                    break;
                }

                continue;
            }

            if (1 !== $curlyBracesLevel || !$token->isArray()) {
                continue;
            }

            if (0 === $bracesLevel && $token->isGivenKind(\T_VARIABLE)) {
                $elements[$index] = [
                    'classIndex' => $classIndex,
                    'token' => $token,
                    'type' => 'property',
                ];

                continue;
            }

            if ($token->isGivenKind(CT::T_PROPERTY_HOOK_BRACE_OPEN)) {
                $index = $this->tokens->getNextTokenOfKind($index, [[CT::T_PROPERTY_HOOK_BRACE_CLOSE]]);

                continue;
            }

            if ($token->isGivenKind(FCT::T_ATTRIBUTE)) {
                $index = $this->tokens->findBlockEnd(Tokens::BLOCK_TYPE_ATTRIBUTE, $index);

                continue;
            }

            if ($token->isGivenKind(\T_FUNCTION)) {
                $functionNameIndex = $this->tokens->getNextMeaningfulToken($index);
                if ($this->tokens[$functionNameIndex]->equals('(')) {
                    continue;
                }
                $elements[$index] = [
                    'classIndex' => $classIndex,
                    'token' => $token,
                    'type' => 'method',
                ];
                if ('__construct' === $this->tokens[$functionNameIndex]->getContent()) {
                    $openParenthesis = $this->tokens->getNextMeaningfulToken($functionNameIndex);
                    $closeParenthesis = $this->tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS, $openParenthesis);
                    foreach ($this->tokens->findGivenKind([CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PUBLIC, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PROTECTED, CT::T_CONSTRUCTOR_PROPERTY_PROMOTION_PRIVATE, FCT::T_PRIVATE_SET, FCT::T_PROTECTED_SET, FCT::T_PUBLIC_SET, FCT::T_READONLY, \T_FINAL], $openParenthesis, $closeParenthesis) as $kindElements) {
                        foreach (array_keys($kindElements) as $promotedPropertyModifierIndex) {
                            /** @var int $promotedPropertyVariableIndex */
                            $promotedPropertyVariableIndex = $this->tokens->getNextTokenOfKind($promotedPropertyModifierIndex, [[\T_VARIABLE]]);
                            $elements[$promotedPropertyVariableIndex] = [
                                'classIndex' => $classIndex,
                                'token' => $this->tokens[$promotedPropertyVariableIndex],
                                'type' => 'promoted_property',
                            ];
                        }
                    }
                }
            } elseif ($token->isGivenKind(\T_CONST)) {
                $elements[$index] = [
                    'classIndex' => $classIndex,
                    'token' => $token,
                    'type' => 'const',
                ];
            } elseif ($token->isGivenKind(CT::T_USE_TRAIT)) {
                $elements[$index] = [
                    'classIndex' => $classIndex,
                    'token' => $token,
                    'type' => 'trait_import',
                ];
            } elseif ($token->isGivenKind(\T_CASE)) {
                $elements[$index] = [
                    'classIndex' => $classIndex,
                    'token' => $token,
                    'type' => 'case',
                ];
            }
        }

        return [$index, $elements];
    }
}
