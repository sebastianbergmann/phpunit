<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\BenevolentUnionType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class OperatorRuleHelper
{

	private RuleLevelHelper $ruleLevelHelper;

	public function __construct(RuleLevelHelper $ruleLevelHelper)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function isValidForArithmeticOperation(Scope $scope, Expr $expr): bool
	{
		$type = $scope->getType($expr);
		if ($type instanceof MixedType) {
			return true;
		}

		// already reported by PHPStan core
		if ($type->toNumber() instanceof ErrorType) {
			return true;
		}

		return $this->isSubtypeOfNumber($scope, $expr);
	}

	public function isValidForIncrement(Scope $scope, Expr $expr): bool
	{
		$type = $scope->getType($expr);
		if ($type instanceof MixedType) {
			return true;
		}

		if ($type->isString()->yes()) {
			// Because `$a = 'a'; $a++;` is valid
			return true;
		}

		return $this->isSubtypeOfNumber($scope, $expr);
	}

	public function isValidForDecrement(Scope $scope, Expr $expr): bool
	{
		$type = $scope->getType($expr);
		if ($type instanceof MixedType) {
			return true;
		}

		return $this->isSubtypeOfNumber($scope, $expr);
	}

	private function isSubtypeOfNumber(Scope $scope, Expr $expr): bool
	{
		$acceptedType = new UnionType([new IntegerType(), new FloatType(), new IntersectionType([new StringType(), new AccessoryNumericStringType()])]);

		$type = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$expr,
			'',
			static fn (Type $type): bool => $acceptedType->isSuperTypeOf($type)->yes(),
		)->getType();

		if ($type instanceof ErrorType) {
			return true;
		}

		$isSuperType = $acceptedType->isSuperTypeOf($type);
		if ($type instanceof BenevolentUnionType) {
			return !$isSuperType->no();
		}

		return $isSuperType->yes();
	}

}
