<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Printer\Printer;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\MixedType;
use Rector\TypePerfect\Configuration;

/**
 * @see \Rector\TypePerfect\Tests\Rules\NoMixedPropertyFetcherRule\NoMixedPropertyFetcherRuleTest
 * @implements Rule<PropertyFetch>
 */
final readonly class NoMixedPropertyFetcherRule implements Rule
{
    public const string ERROR_MESSAGE = 'Mixed property fetch in a "%s->..." can skip important errors. Make sure the type is known';

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
        return PropertyFetch::class;
    }

    /**
     * @param PropertyFetch $node
     * @return RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->configuration->isNoMixedPropertyEnabled()) {
            return [];
        }

        $callerType = $scope->getType($node->var);
        if (! $callerType instanceof MixedType) {
            return [];
        }

        $printedVar = $this->printer->prettyPrintExpr($node->var);

        return [
            RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, $printedVar))
                ->identifier('typePerfect.noMixedPropertyFetcher')
                ->build(),
        ];
    }
}
