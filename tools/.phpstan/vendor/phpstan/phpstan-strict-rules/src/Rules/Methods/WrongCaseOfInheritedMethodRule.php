<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function sprintf;

/**
 * @implements Rule<InClassMethodNode>
 */
class WrongCaseOfInheritedMethodRule implements Rule
{

	public function getNodeType(): string
	{
		return InClassMethodNode::class;
	}

	public function processNode(
		Node $node,
		Scope $scope
	): array
	{
		$methodReflection = $node->getMethodReflection();
		$declaringClass = $methodReflection->getDeclaringClass();

		$messages = [];
		if ($declaringClass->getParentClass() !== null) {
			$parentMessage = $this->findMethod(
				$declaringClass,
				$declaringClass->getParentClass(),
				$methodReflection->getName(),
			);
			if ($parentMessage !== null) {
				$messages[] = $parentMessage;
			}
		}

		foreach ($declaringClass->getInterfaces() as $interface) {
			$interfaceMessage = $this->findMethod(
				$declaringClass,
				$interface,
				$methodReflection->getName(),
			);
			if ($interfaceMessage === null) {
				continue;
			}

			$messages[] = $interfaceMessage;
		}

		return $messages;
	}

	private function findMethod(
		ClassReflection $declaringClass,
		ClassReflection $classReflection,
		string $methodName
	): ?IdentifierRuleError
	{
		if (!$classReflection->hasNativeMethod($methodName)) {
			return null;
		}

		$parentMethod = $classReflection->getNativeMethod($methodName);
		if ($parentMethod->getName() === $methodName) {
			return null;
		}

		return RuleErrorBuilder::message(sprintf(
			'Method %s::%s() does not match %s method name: %s::%s().',
			$declaringClass->getDisplayName(),
			$methodName,
			$classReflection->isInterface() ? 'interface' : 'parent',
			$classReflection->getDisplayName(),
			$parentMethod->getName(),
		))->identifier('method.nameCase')->build();
	}

}
