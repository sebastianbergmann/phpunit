<?php declare(strict_types = 1);

namespace PHPStan\Rules\Deprecations;

use PHPStan\Analyser\Scope;

/**
 * This is the interface for custom deprecated scope resolvers.
 *
 * To register it in the configuration file use the `phpstan.deprecations.deprecatedScopeResolver` service tag:
 *
 * ```
 * services:
 * 	-
 *		class: App\PHPStan\MyExtension
 *		tags:
 *			- phpstan.deprecations.deprecatedScopeResolver
 * ```
 *
 * @api
 */
interface DeprecatedScopeResolver
{

	public function isScopeDeprecated(Scope $scope): bool;

}
