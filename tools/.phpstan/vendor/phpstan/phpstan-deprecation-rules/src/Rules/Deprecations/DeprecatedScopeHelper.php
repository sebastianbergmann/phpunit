<?php declare(strict_types = 1);

namespace PHPStan\Rules\Deprecations;

use PHPStan\Analyser\Scope;

class DeprecatedScopeHelper
{

	/** @var DeprecatedScopeResolver[]  */
	private array $resolvers;

	/**
	 * @param DeprecatedScopeResolver[] $checkers
	 */
	public function __construct(array $checkers)
	{
		$this->resolvers = $checkers;
	}

	public function isScopeDeprecated(Scope $scope): bool
	{
		foreach ($this->resolvers as $checker) {
			if ($checker->isScopeDeprecated($scope)) {
				return true;
			}
		}

		return false;
	}

}
