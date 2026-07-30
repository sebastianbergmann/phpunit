<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use Rector\TypePerfect\Configuration;

/**
 * @see \Rector\TypePerfect\Tests\Rules\NoArrayAccessOnObjectRule\NoArrayAccessOnObjectRuleTest
 * @implements Rule<ArrayDimFetch>
 */
final readonly class NoArrayAccessOnObjectRule implements Rule
{
    public const string ERROR_MESSAGE = 'Use explicit methods over array access on object';

    /**
     * @var string[]
     */
    private const array ALLOWED_CLASSES = [
        'SplFixedArray',
        'SimpleXMLElement',
        'Iterator',
        'WeakMap',
        'Aws\ResultInterface',
        'Symfony\Component\DomCrawler\AbstractUriElement',
        'Symfony\Component\Form\FormInterface',
        'Symfony\Component\OptionsResolver\Options',
    ];

    public function __construct(
        private Configuration $configuration,
    ) {
    }

    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return ArrayDimFetch::class;
    }

    /**
     * @param ArrayDimFetch $node
     * @return RuleError[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $this->configuration->isNoArrayAccessOnObjectEnabled()) {
            return [];
        }

        $varType = $scope->getType($node->var);
        if (! $varType instanceof ObjectType) {
            return [];
        }

        if ($this->isAllowedObjectType($varType)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(self::ERROR_MESSAGE)
                ->identifier('typePerfect.noArrayAccessOnObject')
                ->build(),
        ];
    }

    private function isAllowedObjectType(ObjectType $objectType): bool
    {
        return array_any(self::ALLOWED_CLASSES, fn (string $allowedClass): bool => $objectType->isInstanceOf($allowedClass)->yes());
    }
}
