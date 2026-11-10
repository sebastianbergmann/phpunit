<?php declare(strict_types = 1);

namespace PHPStan\Rules\Deprecations;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\FunctionNotFoundException;
use PHPStan\Php\PhpVersion;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function array_key_exists;
use function count;
use function in_array;
use function sprintf;
use function strtolower;

/**
 * @implements Rule<FuncCall>
 */
class CallWithDeprecatedIniOptionRule implements Rule
{

	private const INI_FUNCTIONS = [
		'ini_get',
		'ini_set',
		'ini_alter',
		'ini_restore',
		'get_cfg_var',
	];

	private const DEPRECATED_OPTIONS = [
		// deprecated since unknown version
		'mbstring.http_input' => 0,
		'mbstring.http_output' => 0,
		'mbstring.internal_encoding' => 0,
		'pdo_odbc.db2_instance_name' => 0,
		'enable_dl' => 0,

		'iconv.input_encoding' => 50600,
		'iconv.output_encoding' => 50600,
		'iconv.internal_encoding' => 50600,

		'mbstring.func_overload' => 70200,
		'track_errors' => 70200,

		'allow_url_include' => 70400,

		'assert.quiet_eval' => 80000,

		'filter.default' => 80100,
		'oci8.old_oci_close_semantics' => 80100,

		'assert.active' => 80300,
		'assert.exception' => 80300,
		'assert.bail' => 80300,
		'assert.warning' => 80300,

		'session.sid_length' => 80400,
		'session.sid_bits_per_character' => 80400,
	];

	private ReflectionProvider $reflectionProvider;

	private DeprecatedScopeHelper $deprecatedScopeHelper;

	private PhpVersion $phpVersion;

	public function __construct(
		ReflectionProvider $reflectionProvider,
		DeprecatedScopeHelper $deprecatedScopeHelper,
		PhpVersion $phpVersion
	)
	{
		$this->reflectionProvider = $reflectionProvider;
		$this->deprecatedScopeHelper = $deprecatedScopeHelper;
		$this->phpVersion = $phpVersion;
	}

	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($this->deprecatedScopeHelper->isScopeDeprecated($scope)) {
			return [];
		}

		if (!($node->name instanceof Name)) {
			return [];
		}

		if (count($node->getArgs()) < 1) {
			return [];
		}

		try {
			$function = $this->reflectionProvider->getFunction($node->name, $scope);
		} catch (FunctionNotFoundException $e) {
			// Other rules will notify if the function is not found
			return [];
		}

		if (!in_array(strtolower($function->getName()), self::INI_FUNCTIONS, true)) {
			return [];
		}

		$phpVersionId = $this->phpVersion->getVersionId();
		$iniType = $scope->getType($node->getArgs()[0]->value);
		foreach ($iniType->getConstantStrings() as $string) {
			if (!array_key_exists($string->getValue(), self::DEPRECATED_OPTIONS)) {
				continue;
			}

			if ($phpVersionId < self::DEPRECATED_OPTIONS[$string->getValue()]) {
				continue;
			}

			return [
				RuleErrorBuilder::message(sprintf(
					"Call to function %s() with deprecated option '%s'.",
					$function->getName(),
					$string->getValue(),
				))->identifier('function.deprecated')->build(),
			];
		}

		return [];
	}

}
