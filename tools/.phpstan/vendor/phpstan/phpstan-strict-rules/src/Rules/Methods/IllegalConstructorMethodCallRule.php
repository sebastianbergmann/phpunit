<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node\Expr\MethodCall>
 */
final class IllegalConstructorMethodCallRule implements Rule
{

	public function getNodeType(): string
	{
		return Node\Expr\MethodCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Node\Identifier || $node->name->toLowerString() !== '__construct') {
			return [];
		}

		return [
			RuleErrorBuilder::message('Call to __construct() on an existing object is not allowed.')
				->identifier('constructor.call')
				->build(),
		];
	}

}
