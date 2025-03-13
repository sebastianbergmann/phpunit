<?php

declare(strict_types=1);

namespace TomasVotruba\TypeCoverage\Collectors;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;

/**
 * @see \TomasVotruba\TypeCoverage\Rules\ParamTypeCoverageRule
 */
final class ParamTypeDeclarationCollector implements Collector
{
    public function getNodeType(): string
    {
        return FunctionLike::class;
    }

    /**
     * @param FunctionLike $node
     * @return mixed[]|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if ($this->shouldSkipFunctionLike($node)) {
            return null;
        }

        $missingTypeLines = [];
        $paramCount = count($node->getParams());

        foreach ($node->getParams() as $param) {
            if ($param->variadic) {
                // skip variadic
                --$paramCount;
                continue;
            }

            if ($param->type === null) {
                $missingTypeLines[] = $param->getLine();
            }
        }

        return [$paramCount, $missingTypeLines];
    }

    private function shouldSkipFunctionLike(FunctionLike $functionLike): bool
    {
        // nothing to analyse
        if ($functionLike->getParams() === []) {
            return true;
        }

        return $this->hasFunctionLikeCallableParam($functionLike);
    }

    private function hasFunctionLikeCallableParam(FunctionLike $functionLike): bool
    {
        // skip callable, can be anythings
        $docComment = $functionLike->getDocComment();
        if (! $docComment instanceof Doc) {
            return false;
        }

        $docCommentText = $docComment->getText();
        return strpos($docCommentText, '@param callable') !== false;
    }
}
