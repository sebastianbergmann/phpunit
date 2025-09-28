<?php declare(strict_types = 1);

namespace PHPStan\Rules\Classes;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionClass;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionEnum;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;
use function property_exists;
use function sprintf;

/**
 * @implements Rule<ClassMethod>
 */
class RequireParentConstructCallRule implements Rule
{

	public function getNodeType(): string
	{
		return ClassMethod::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->isInClass()) {
			throw new ShouldNotHappenException();
		}

		if ($scope->isInTrait()) {
			return [];
		}

		if ($node->name->name !== '__construct') {
			return [];
		}

		if ($node->isAbstract()) {
			return [];
		}

		$classReflection = $scope->getClassReflection()->getNativeReflection();
		if ($classReflection->isInterface() || $classReflection->isAnonymous()) {
			return [];
		}

		if ($this->callsParentConstruct($node)) {
			return [];
		}

		$parentClass = $this->getParentConstructorClass($classReflection);
		if ($parentClass !== false) {
			return [
				RuleErrorBuilder::message(sprintf(
					'%s::__construct() does not call parent constructor from %s.',
					$classReflection->getName(),
					$parentClass->getName(),
				))->identifier('constructor.missingParentCall')->build(),
			];
		}

		return [];
	}

	private function callsParentConstruct(Node $parserNode): bool
	{
		if (!property_exists($parserNode, 'stmts')) {
			return false;
		}

		foreach ($parserNode->stmts as $statement) {
			if ($statement instanceof Node\Stmt\Expression) {
				$statement = $statement->expr;
			}

			$statement = $this->ignoreErrorSuppression($statement);
			if ($statement instanceof StaticCall) {
				if (
					$statement->class instanceof Name
					&& ((string) $statement->class === 'parent')
					&& $statement->name instanceof Node\Identifier
					&& $statement->name->name === '__construct'
				) {
					return true;
				}
			} else {
				if ($this->callsParentConstruct($statement)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @param ReflectionClass|ReflectionEnum $classReflection
	 * @return ReflectionClass|false
	 */
	private function getParentConstructorClass($classReflection)
	{
		$parentClass = $classReflection->getParentClass();
		while ($parentClass !== false) {
			$constructor = $parentClass->hasMethod('__construct') ? $parentClass->getMethod('__construct') : null;
			$constructorWithClassName = $parentClass->hasMethod($parentClass->getName()) ? $parentClass->getMethod($parentClass->getName()) : null;
			if (
				(
					$constructor !== null
					&& $constructor->getDeclaringClass()->getName() === $parentClass->getName()
					&& !$constructor->isAbstract()
					&& !$constructor->isPrivate()
					&& !$constructor->isDeprecated()
				) || (
					$constructorWithClassName !== null
					&& $constructorWithClassName->getDeclaringClass()->getName() === $parentClass->getName()
					&& !$constructorWithClassName->isAbstract()
				)
			) {
				return $parentClass;
			}

			$parentClass = $parentClass->getParentClass();
		}

		return false;
	}

	private function ignoreErrorSuppression(Node $statement): Node
	{
		if ($statement instanceof Node\Expr\ErrorSuppress) {

			return $statement->expr;
		}

		return $statement;
	}

}
