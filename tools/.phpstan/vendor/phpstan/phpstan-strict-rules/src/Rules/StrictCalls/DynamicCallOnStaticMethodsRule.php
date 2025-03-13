<?php declare(strict_types = 1);

namespace PHPStan\Rules\StrictCalls;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Testing\PHPStanTestCase;
use PHPStan\Testing\TypeInferenceTestCase;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use function in_array;
use function sprintf;

/**
 * @implements Rule<MethodCall>
 */
class DynamicCallOnStaticMethodsRule implements Rule
{

	private RuleLevelHelper $ruleLevelHelper;

	public function __construct(RuleLevelHelper $ruleLevelHelper)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Node\Identifier) {
			return [];
		}

		$name = $node->name->name;
		$type = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$node->var,
			'',
			static fn (Type $type): bool => $type->canCallMethods()->yes() && $type->hasMethod($name)->yes(),
		)->getType();

		if ($type instanceof ErrorType || !$type->canCallMethods()->yes() || !$type->hasMethod($name)->yes()) {
			return [];
		}

		$methodReflection = $type->getMethod($name, $scope);
		if ($methodReflection->isStatic()) {
			$prototype = $methodReflection->getPrototype();
			if (in_array($prototype->getDeclaringClass()->getName(), [
				TypeInferenceTestCase::class,
				PHPStanTestCase::class,
			], true)) {
				return [];
			}

			return [
				RuleErrorBuilder::message(sprintf(
					'Dynamic call to static method %s::%s().',
					$methodReflection->getDeclaringClass()->getDisplayName(),
					$methodReflection->getName(),
				))->identifier('staticMethod.dynamicCall')->build(),
			];
		}

		return [];
	}

}
