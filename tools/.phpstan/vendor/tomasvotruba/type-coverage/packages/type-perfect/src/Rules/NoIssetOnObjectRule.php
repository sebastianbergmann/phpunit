<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Isset_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use Rector\TypePerfect\Configuration;
use Rector\TypePerfect\Guard\EmptyIssetGuard;

/**
 * @see \Rector\TypePerfect\Tests\Rules\NoIssetOnObjectRule\NoIssetOnObjectRuleTest
 * @implements Rule<Isset_>
 */
final readonly class NoIssetOnObjectRule implements Rule
{
    public const string ERROR_MESSAGE = 'Use instanceof instead of isset() on object';

    public function __construct(
        private EmptyIssetGuard $emptyIssetGuard,
        private Configuration $configuration,
    ) {
    }

    public function getNodeType(): string
    {
        return Isset_::class;
    }

    /**
     * @param Isset_ $node
     *
     * @return RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->configuration->isNoIssetOnObjectEnabled()) {
            return [];
        }

        foreach ($node->vars as $var) {
            if ($this->emptyIssetGuard->isLegal($var, $scope)) {
                continue;
            }

            return [
                RuleErrorBuilder::message(self::ERROR_MESSAGE)
                    ->identifier('typePerfect.noIssetOnObject')
                    ->build(),
            ];
        }

        return [];
    }
}
