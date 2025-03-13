<?php declare(strict_types = 1);

namespace PHPStan\Rules\StrictCalls;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
use function array_key_exists;
use function sprintf;
use function strtolower;

/**
 * @implements Rule<FuncCall>
 */
class StrictFunctionCallsRule implements Rule
{

	/** @var int[] */
	private array $functionArguments = [
		'in_array' => 2,
		'array_search' => 2,
		'base64_decode' => 1,
		'array_keys' => 2,
	];

	private ReflectionProvider $reflectionProvider;

	public function __construct(ReflectionProvider $reflectionProvider)
	{
		$this->reflectionProvider = $reflectionProvider;
	}

	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Name) {
			return [];
		}

		if (!$this->reflectionProvider->hasFunction($node->name, $scope)) {
			return [];
		}

		$function = $this->reflectionProvider->getFunction($node->name, $scope);
		$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs($scope, $node->getArgs(), $function->getVariants());
		$node = ArgumentsNormalizer::reorderFuncArguments($parametersAcceptor, $node);
		if ($node === null) {
			return [];
		}
		$functionName = strtolower($function->getName());
		if (!array_key_exists($functionName, $this->functionArguments)) {
			return [];
		}

		if ($functionName === 'array_keys' && !array_key_exists(1, $node->getArgs())) {
			return [];
		}

		$argumentPosition = $this->functionArguments[$functionName];
		if (!array_key_exists($argumentPosition, $node->getArgs())) {
			return [
				RuleErrorBuilder::message(sprintf(
					'Call to function %s() requires parameter #%d to be set.',
					$functionName,
					$argumentPosition + 1,
				))->identifier('function.strict')->build(),
			];
		}

		$argumentType = $scope->getType($node->getArgs()[$argumentPosition]->value);
		$trueType = new ConstantBooleanType(true);
		if (!$trueType->isSuperTypeOf($argumentType)->yes()) {
			return [
				RuleErrorBuilder::message(sprintf(
					'Call to function %s() requires parameter #%d to be true.',
					$functionName,
					$argumentPosition + 1,
				))->identifier('function.strict')->build(),
			];
		}

		return [];
	}

}
