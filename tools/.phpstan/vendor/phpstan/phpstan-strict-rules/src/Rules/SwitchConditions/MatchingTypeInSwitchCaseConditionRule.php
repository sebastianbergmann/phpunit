<?php declare(strict_types = 1);

namespace PHPStan\Rules\SwitchConditions;

use PhpParser\Node;
use PhpParser\Node\Stmt\Switch_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Printer\Printer;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @implements Rule<Switch_>
 */
class MatchingTypeInSwitchCaseConditionRule implements Rule
{

	private Printer $printer;

	public function __construct(Printer $printer)
	{
		$this->printer = $printer;
	}

	public function getNodeType(): string
	{
		return Switch_::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$messages = [];
		$conditionType = $scope->getType($node->cond);
		foreach ($node->cases as $case) {
			if ($case->cond === null) {
				continue;
			}

			$caseType = $scope->getType($case->cond);
			if (!$conditionType->isSuperTypeOf($caseType)->no()) {
				continue;
			}

			$messages[] = RuleErrorBuilder::message(sprintf(
				'Switch condition type (%s) does not match case condition %s (%s).',
				$conditionType->describe(VerbosityLevel::value()),
				$this->printer->prettyPrintExpr($case->cond),
				$caseType->describe(VerbosityLevel::typeOnly()),
			))
				->line($case->getStartLine())
				->identifier('switch.type')
				->build();
		}

		return $messages;
	}

}
