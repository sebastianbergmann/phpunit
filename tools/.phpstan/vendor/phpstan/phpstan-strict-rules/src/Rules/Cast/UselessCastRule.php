<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ErrorType;
use PHPStan\Type\GeneralizePrecision;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @implements Rule<Cast>
 */
class UselessCastRule implements Rule
{

	private bool $treatPhpDocTypesAsCertain;

	private bool $treatPhpDocTypesAsCertainTip;

	public function __construct(
		bool $treatPhpDocTypesAsCertain,
		bool $treatPhpDocTypesAsCertainTip
	)
	{
		$this->treatPhpDocTypesAsCertain = $treatPhpDocTypesAsCertain;
		$this->treatPhpDocTypesAsCertainTip = $treatPhpDocTypesAsCertainTip;
	}

	public function getNodeType(): string
	{
		return Cast::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$castType = $scope->getType($node);
		if ($castType instanceof ErrorType) {
			return [];
		}
		$castType = $castType->generalize(GeneralizePrecision::lessSpecific());

		if ($this->treatPhpDocTypesAsCertain) {
			$expressionType = $scope->getType($node->expr);
		} else {
			$expressionType = $scope->getNativeType($node->expr);
		}
		if ($castType->isSuperTypeOf($expressionType)->yes()) {
			$addTip = function (RuleErrorBuilder $ruleErrorBuilder) use ($scope, $node, $castType): RuleErrorBuilder {
				if (!$this->treatPhpDocTypesAsCertain) {
					return $ruleErrorBuilder;
				}

				$expressionTypeWithoutPhpDoc = $scope->getNativeType($node->expr);
				if ($castType->isSuperTypeOf($expressionTypeWithoutPhpDoc)->yes()) {
					return $ruleErrorBuilder;
				}

				if (!$this->treatPhpDocTypesAsCertainTip) {
					return $ruleErrorBuilder;
				}

				return $ruleErrorBuilder->treatPhpDocTypesAsCertainTip();
			};
			return [
				$addTip(RuleErrorBuilder::message(sprintf(
					'Casting to %s something that\'s already %s.',
					$castType->describe(VerbosityLevel::typeOnly()),
					$expressionType->describe(VerbosityLevel::typeOnly()),
				)))->identifier('cast.useless')->build(),
			];
		}

		return [];
	}

}
