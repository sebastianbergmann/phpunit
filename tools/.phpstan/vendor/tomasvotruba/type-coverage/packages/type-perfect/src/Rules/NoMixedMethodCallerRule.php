<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Printer\Printer;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use Rector\TypePerfect\Configuration;

/**
 * @see \Rector\TypePerfect\Tests\Rules\NoMixedMethodCallerRule\NoMixedMethodCallerRuleTest
 * @implements Rule<MethodCall>
 */
final readonly class NoMixedMethodCallerRule implements Rule
{
    public const string ERROR_MESSAGE = 'Mixed variable in a `%s->...()` can skip important errors. Make sure the type is known';

    public function __construct(
        private Printer $printer,
        private Configuration $configuration,
    ) {
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     * @return RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->configuration->isNoMixedCallerEnabled()) {
            return [];
        }

        $callerType = $scope->getType($node->var);

        if (! $callerType instanceof MixedType) {
            return [];
        }

        if ($callerType instanceof ErrorType) {
            return [];
        }

        // if error, skip as well for false positive
        if ($this->isPreviousTypeErrorType($node, $scope)) {
            return [];
        }

        $printedMethodCall = $this->printer->prettyPrintExpr($node->var);

        return [
            RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, $printedMethodCall))
                ->identifier('typePerfect.noMixedMethodCaller')
                ->build(),
        ];
    }

    private function isPreviousTypeErrorType(MethodCall $methodCall, Scope $scope): bool
    {
        $currentMethodCall = $methodCall;
        while ($currentMethodCall->var instanceof MethodCall) {
            $previousType = $scope->getType($currentMethodCall->var);
            if ($previousType instanceof ErrorType) {
                return true;
            }

            $currentMethodCall = $currentMethodCall->var;
        }

        return false;
    }
}
