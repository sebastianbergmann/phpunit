<?php declare(strict_types = 1);

namespace Composer\Pcre\PHPStan;

use Composer\Pcre\Preg;
use Composer\Pcre\Regex;
use Composer\Pcre\PcreException;
use Nette\Utils\RegexpException;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function in_array;
use function sprintf;

/**
 * Copy of PHPStan's RegularExpressionPatternRule
 *
 * @implements Rule<StaticCall>
 */
class InvalidRegexPatternRule implements Rule
{
    public function getNodeType(): string
    {
        return StaticCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $patterns = $this->extractPatterns($node, $scope);

        $errors = [];
        foreach ($patterns as $pattern) {
            $errorMessage = $this->validatePattern($pattern);
            if ($errorMessage === null) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf('Regex pattern is invalid: %s', $errorMessage))->identifier('regexp.pattern')->build();
        }

        return $errors;
    }

    /**
     * @return string[]
     */
    private function extractPatterns(StaticCall $node, Scope $scope): array
    {
        if (!$node->class instanceof FullyQualified) {
            return [];
        }
        $isRegex = $node->class->toString() === Regex::class;
        $isPreg = $node->class->toString() === Preg::class;
        if (!$isRegex && !$isPreg) {
            return [];
        }
        if (!$node->name instanceof Node\Identifier || !Preg::isMatch('{^(match|isMatch|grep|replace|split)}', $node->name->name)) {
            return [];
        }

        $functionName = $node->name->name;
        if (!isset($node->getArgs()[0])) {
            return [];
        }

        $patternNode = $node->getArgs()[0]->value;
        $patternType = $scope->getType($patternNode);

        $patternStrings = [];

        foreach ($patternType->getConstantStrings() as $constantStringType) {
            if ($functionName === 'replaceCallbackArray') {
                continue;
            }

            $patternStrings[] = $constantStringType->getValue();
        }

        foreach ($patternType->getConstantArrays() as $constantArrayType) {
            if (
                in_array($functionName, [
                    'replace',
                    'replaceCallback',
                ], true)
            ) {
                foreach ($constantArrayType->getValueTypes() as $arrayKeyType) {
                    foreach ($arrayKeyType->getConstantStrings() as $constantString) {
                        $patternStrings[] = $constantString->getValue();
                    }
                }
            }

            if ($functionName !== 'replaceCallbackArray') {
                continue;
            }

            foreach ($constantArrayType->getKeyTypes() as $arrayKeyType) {
                foreach ($arrayKeyType->getConstantStrings() as $constantString) {
                    $patternStrings[] = $constantString->getValue();
                }
            }
        }

        return $patternStrings;
    }

    private function validatePattern(string $pattern): ?string
    {
        try {
            $msg = null;
            $prev = set_error_handler(function (int $severity, string $message, string $file) use (&$msg): bool {
                $msg = preg_replace("#^preg_match(_all)?\\(.*?\\): #", '', $message);

                return true;
            });

            if ($pattern === '') {
                return 'Empty string is not a valid regular expression';
            }

            Preg::match($pattern, '');
            if ($msg !== null) {
                return $msg;
            }
        } catch (PcreException $e) {
            if ($e->getCode() === PREG_INTERNAL_ERROR && $msg !== null) {
                return $msg;
            }

            return preg_replace('{.*? failed executing ".*": }', '', $e->getMessage());
        } finally {
            restore_error_handler();
        }

        return null;
    }

}
