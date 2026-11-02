<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Void_;
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
		if ($node instanceof Void_) {
			return [];
		}

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
			$ruleErrorBuilder = RuleErrorBuilder::message(sprintf(
				'Casting to %s something that\'s already %s.',
				$castType->describe(VerbosityLevel::typeOnly()),
				$expressionType->describe(VerbosityLevel::typeOnly()),
			))->identifier('cast.useless');

			$nativeType = $scope->getNativeType($node->expr);
			if ($castType->isSuperTypeOf($nativeType)->yes()) {
				$ruleErrorBuilder->fixNode($node, static function (Cast $node) use ($nativeType): Node {
					if ($node instanceof Cast\Bool_) {
						if (!$nativeType->isBoolean()->yes()) {
							return $node;
						}
					} elseif ($node instanceof Cast\Int_) {
						if (
							$node->expr instanceof Node\Expr\UnaryMinus
							|| !$nativeType->isInteger()->yes()
						) {
							return $node;
						}
					} else {
						// skip all other casts
						return $node;
					}

					return $node->expr;
				});
			} elseif ($this->treatPhpDocTypesAsCertain && $this->treatPhpDocTypesAsCertainTip) {
				$ruleErrorBuilder->treatPhpDocTypesAsCertainTip();
			}

			return [
				$ruleErrorBuilder->build(),
			];
		}

		return [];
	}

}
