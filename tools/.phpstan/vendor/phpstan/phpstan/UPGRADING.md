Upgrading from PHPStan 1.x to 2.0
=================================

## PHP version requirements

PHPStan now requires PHP 7.4 or newer to run.

## Upgrading guide for end users

The best way to get ready for upgrade to PHPStan 2.0 is to update to the **latest PHPStan 1.12 release**
and enable [**Bleeding Edge**](https://phpstan.org/blog/what-is-bleeding-edge). This will enable the new rules and behaviours that 2.0 turns on for all users.

Also make sure to install and enable [`phpstan/phpstan-deprecation-rules`](https://github.com/phpstan/phpstan-deprecation-rules).

Once you get to a green build with no deprecations showed on latest PHPStan 1.12.x with Bleeding Edge enabled, you can update all your related PHPStan dependencies to 2.0 in `composer.json`:

```json
"require-dev": {
    "phpstan/phpstan": "^2.0",
    "phpstan/phpstan-deprecation-rules": "^2.0",
    "phpstan/phpstan-doctrine": "^2.0",
    "phpstan/phpstan-nette": "^2.0",
    "phpstan/phpstan-phpunit": "^2.0",
    "phpstan/phpstan-strict-rules": "^2.0",
    "phpstan/phpstan-symfony": "^2.0",
    "phpstan/phpstan-webmozart-assert": "^2.0",
    ...
}
```

Don't forget to update [3rd party PHPStan extensions](https://phpstan.org/user-guide/extension-library) as well.

After changing your `composer.json`, run `composer update 'phpstan/*' -W`.

It's up to you whether you go through the new reported errors or if you just put them all to the [baseline](https://phpstan.org/user-guide/baseline) ;) Everyone who's on PHPStan 1.12 should be able to upgrade to PHPStan 2.0.

### Noteworthy changes to code analysis

* [**Enhancements in handling parameters passed by reference**](https://phpstan.org/blog/enhancements-in-handling-parameters-passed-by-reference)
* [**Validate inline PHPDoc `@var` tag type**](https://phpstan.org/blog/phpstan-1-10-comes-with-lie-detector#validate-inline-phpdoc-%40var-tag-type)
* [**List type enforced**](https://phpstan.org/blog/phpstan-1-9-0-with-phpdoc-asserts-list-type#list-type)
* **Always `true` conditions always reported**: previously reported only with phpstan-strict-rules, this is now always reported.

### Removed option `checkMissingIterableValueType`

It's strongly recommended to add the missing array typehints.

If you want to continue ignoring missing typehints from arrays, add `missingType.iterableValue` error identifier to your `ignoreErrors`:

```neon
parameters:
	ignoreErrors:
		-
			identifier: missingType.iterableValue
```

### Removed option `checkGenericClassInNonGenericObjectType`

It's strongly recommended to add the missing generic typehints.

If you want to continue ignoring missing typehints from generics, add `missingType.generics` error identifier to your `ignoreErrors`:

```neon
parameters:
	ignoreErrors:
		-
			identifier: missingType.generics
```

### Removed `checkAlwaysTrue*` options

These options have been removed because PHPStan now always behaves as if these were set to `true`:

* `checkAlwaysTrueCheckTypeFunctionCall`
* `checkAlwaysTrueInstanceof`
* `checkAlwaysTrueStrictComparison`
* `checkAlwaysTrueLooseComparison`

### Removed option `excludes_analyse`

It has been replaced with [`excludePaths`](https://phpstan.org/user-guide/ignoring-errors#excluding-whole-files).

### Paths in `excludePaths` and `ignoreErrors` have to be a valid file path or a fnmatch pattern

If you are excluding a file path that might not exist but you still want to have it in `excludePaths`, append `(?)`:

```neon
parameters:
	excludePaths:
		- tests/*/data/*
		- src/broken
		- node_modules (?) # optional path, might not exist
```

If you have the same situation in `ignoreErrors` (ignoring an error in a path that might not exist), use `reportUnmatchedIgnoredErrors: false`.

```neon
parameters:
	reportUnmatchedIgnoredErrors: false
```

Appending `(?)` in `ignoreErrors` is not supported.

### Changes in 1st party PHPStan extensions

* [phpstan-doctrine](https://github.com/phpstan/phpstan-doctrine)
  * Removed config parameter `searchOtherMethodsForQueryBuilderBeginning` (extension now behaves as when this was set to `true`)
  * Removed config parameter `queryBuilderFastAlgorithm` (extension now behaves as when this was set to `false`)
* [phpstan-symfony](https://github.com/phpstan/phpstan-symfony)
  * Removed legacy options with `_` in the name
  * `container_xml_path` -> use `containerXmlPath`
  * `constant_hassers` -> use `constantHassers`
  * `console_application_loader` -> use `consoleApplicationLoader`

### Minor backward compatibility breaks

* Removed unused config parameter `cache.nodesByFileCountMax`
* Removed unused config parameter `memoryLimitFile`
* Removed unused feature toggle `disableRuntimeReflectionProvider`
* Removed unused config parameter `staticReflectionClassNamePatterns`
* Remove `fixerTmpDir` config parameter, use `pro.tmpDir` instead
* Remove `tempResultCachePath` config parameter, use `resultCachePath` instead
* `additionalConfigFiles` config parameter must be a list

## Upgrading guide for extension developers

> [!NOTE]
> Please switch to PHPStan 2.0 in a new major version of your extension. It's not feasible to try to support both PHPStan 1.x and PHPStan 2.x with the same extension code.
>
> You can definitely get closer to supporting PHPStan 2.0 without increasing major version by solving reported deprecations and other issues by analysing your extension code with PHPStan & phpstan-deprecation-rules & Bleeding Edge, but the final leap and solving backward incompatibilities should be done by requiring `"phpstan/phpstan": "^2.0"` in your `composer.json`, and releasing a new major version.

### PHPStan now uses nikic/php-parser v5

See [UPGRADING](https://github.com/nikic/PHP-Parser/blob/master/UPGRADE-5.0.md) guide for PHP-Parser.

The most notable change is how `throw` statement is represented. Previously, `throw` statements like `throw $e;` were represented using the `Stmt\Throw_` class, while uses inside other expressions (such as `$x ?? throw $e`) used the `Expr\Throw_` class.

Now, `throw $e;` is represented as a `Stmt\Expression` that contains an `Expr\Throw_`. The
`Stmt\Throw_` class has been removed.

### PHPStan now uses phpstan/phpdoc-parser v2

See [UPGRADING](https://github.com/phpstan/phpdoc-parser/blob/2.0.x/UPGRADING.md) guide for phpstan/phpdoc-parser.

### Returning plain strings as errors no longer supported, use RuleErrorBuilder

Identifiers are also required in custom rules.

Learn more: [Using RuleErrorBuilder to enrich reported errors in custom rules](https://phpstan.org/blog/using-rule-error-builder)

**Before**:

```php
return ['My error'];
```

**After**:

```php
return [
    RuleErrorBuilder::message('My error')
        ->identifier('my.error')
        ->build(),
];
```

### Deprecate various `instanceof *Type` in favour of new methods on `Type` interface

Learn more: [Why Is instanceof *Type Wrong and Getting Deprecated?](https://phpstan.org/blog/why-is-instanceof-type-wrong-and-getting-deprecated)

### Removed deprecated `ParametersAcceptorSelector::selectSingle()`

Use [`ParametersAcceptorSelector::selectFromArgs()`](https://apiref.phpstan.org/2.0.x/PHPStan.Reflection.ParametersAcceptorSelector.html#_selectFromArgs) instead. It should be used in most places where `selectSingle()` was previously used, like dynamic return type extensions.

**Before**:

```php
$defaultReturnType = ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
```

**After**:

```php
$defaultReturnType = ParametersAcceptorSelector::selectFromArgs(
    $scope,
    $functionCall->getArgs(),
    $functionReflection->getVariants()
)->getReturnType();
```

If you're analysing function or method body itself and you're using one of the following methods, ask for `getParameters()` and `getReturnType()` directly on the reflection object:

* [InClassMethodNode::getMethodReflection()](https://apiref.phpstan.org/2.0.x/PHPStan.Node.InClassMethodNode.html)
* [InFunctionNode::getFunctionReflection()](https://apiref.phpstan.org/2.0.x/PHPStan.Node.InFunctionNode.html)
* [FunctionReturnStatementsNode::getFunctionReflection()](https://apiref.phpstan.org/2.0.x/PHPStan.Node.FunctionReturnStatementsNode.html)
* [MethodReturnStatementsNode::getMethodReflection()](https://apiref.phpstan.org/2.0.x/PHPStan.Node.MethodReturnStatementsNode.html)
* [Scope::getFunction()](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.Scope.html#_getFunction)

**Before**:

```php
$function = $node->getFunctionReflection();
$returnType = ParametersAcceptorSelector::selectSingle($function->getVariants())->getReturnType();
```

**After**:

```php
$returnType = $node->getFunctionReflection()->getReturnType();
```

### Changed `TypeSpecifier::create()` and `SpecifiedTypes` constructor parameters

[`PHPStan\Analyser\TypeSpecifier::create()`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.TypeSpecifier.html#_create) now accepts (all parameters are required):

* `Expr $expr`
* `Type $type`
* `TypeSpecifierContext $context`
* `Scope $scope`

If you want to change `$overwrite` or `$rootExpr` (previous parameters also used to be accepted by this method), call `setAlwaysOverwriteTypes()` and `setRootExpr()` on [`SpecifiedTypes`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.SpecifiedTypes.html) (object returned by `TypeSpecifier::create()`). These methods return a new object (SpecifiedTypes is immutable).

[`SpecifiedTypes`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.SpecifiedTypes.html) constructor now accepts:

* `array $sureTypes`
* `array $sureNotTypes`

If you want to change `$overwrite` or `$rootExpr` (previous parameters also used to be accepted by the constructor), call `setAlwaysOverwriteTypes()` and `setRootExpr()`. These methods return a new object (SpecifiedTypes is immutable).

### `ConstantArrayType` no longer extends `ArrayType`

`Type::getArrays()` now returns `list<ArrayType|ConstantArrayType>`.

Using `$type instanceof ArrayType` is [being deprecated anyway](https://phpstan.org/blog/why-is-instanceof-type-wrong-and-getting-deprecated) so the impact of this change should be minimal.

### Changed `TypeSpecifier::specifyTypesInCondition()`

This method no longer accepts `Expr $rootExpr`. If you want to change it, call `setRootExpr()` on [`SpecifiedTypes`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.SpecifiedTypes.html) (object returned by `TypeSpecifier::specifyTypesInCondition()`). `setRootExpr()` method returns a new object (SpecifiedTypes is immutable).

### Node attributes `parent`, `previous`, `next` are no longer available

Learn more: https://phpstan.org/blog/preprocessing-ast-for-custom-rules

### Removed config parameter `scopeClass`

As a replacement you can implement [`PHPStan\Type\ExpressionTypeResolverExtension`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.ExpressionTypeResolverExtension.html) interface instead and register it as a service.

### Removed `PHPStan\Broker\Broker`

Use [`PHPStan\Reflection\ReflectionProvider`](https://apiref.phpstan.org/2.0.x/PHPStan.Reflection.ReflectionProvider.html) instead.

`BrokerAwareExtension` was also removed. Ask for `ReflectionProvider` in the extension constructor instead.

Instead of `PHPStanTestCase::createBroker()`, call `PHPStanTestCase::createReflectionProvider()`.

### List type is enabled for everyone

Removed static methods from `AccessoryArrayListType` class:

* `isListTypeEnabled()`
* `setListTypeEnabled()`
* `intersectWith()`

Instead of `AccessoryArrayListType::intersectWith($type)`, do `TypeCombinator::intersect($type, new AccessoryArrayListType())`.

### Minor backward compatibility breaks

* Classes that were previously `@final` were made `final`
* Parameter `$callableParameters` of [`MutatingScope::enterAnonymousFunction()`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.MutatingScope.html#_enterAnonymousFunction) and [`enterArrowFunction()`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.MutatingScope.html#_enterArrowFunction) made required
* Parameter `StatementContext $context` of [`NodeScopeResolver::processStmtNodes()`](https://apiref.phpstan.org/2.0.x/PHPStan.Analyser.NodeScopeResolver.html#_processStmtNodes) made required
* ClassPropertiesNode - remove `$extensions` parameter from [`getUninitializedProperties()`](https://apiref.phpstan.org/2.0.x/PHPStan.Node.ClassPropertiesNode.html#_getUninitializedProperties)
* `Type::getSmallerType()`, `Type::getSmallerOrEqualType()`, `Type::getGreaterType()`, `Type::getGreaterOrEqualType()`, `Type::isSmallerThan()`, `Type::isSmallerThanOrEqual()` now require [`PhpVersion`](https://apiref.phpstan.org/2.0.x/PHPStan.Php.PhpVersion.html) as argument.
* `CompoundType::isGreaterThan()`, `CompoundType::isGreaterThanOrEqual()` now require [`PhpVersion`](https://apiref.phpstan.org/2.0.x/PHPStan.Php.PhpVersion.html) as argument.
* Removed `ReflectionProvider::supportsAnonymousClasses()` (all reflection providers support anonymous classes)
* Remove `ArrayType::generalizeKeys()`
* Remove `ArrayType::count()`, use `Type::getArraySize()` instead
* Remove `ArrayType::castToArrayKeyType()`, `Type::toArrayKey()` instead
* Remove `UnionType::pickTypes()`, use `pickFromTypes()` instead
* Remove `RegexArrayShapeMatcher::matchType()`, use `matchExpr()` instead
* Remove unused `PHPStanTestCase::$useStaticReflectionProvider`
* Remove `PHPStanTestCase::getReflectors()`, use `getReflector()` instead
* Remove `ClassReflection::getFileNameWithPhpDocs()`, use `getFileName()` instead
* Remove `AnalysisResult::getInternalErrors()`, use `getInternalErrorObjects()` instead
* Remove `ConstantReflection::getValue()`, use `getValueExpr()` instead. To get `Type` from `Expr`, use `Scope::getType()` or `InitializerExprTypeResolver::getType()`
* Remove `PropertyTag::getType()`, use `getReadableType()` / `getWritableType()` instead
* Remove `GenericTypeVariableResolver`, use [`Type::getTemplateType()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_getTemplateType) instead
* Rename `Type::isClassStringType()` to `Type::isClassString()`
* Remove `Scope::isSpecified()`, use `hasExpressionType()` instead
* Remove `ConstantArrayType::isEmpty()`, use `isIterableAtLeastOnce()->no()` instead
* Remove `ConstantArrayType::getNextAutoIndex()`
* Removed methods from `ConstantArrayType` - `getFirst*Type` and `getLast*Type`
  * Use `getFirstIterable*Type` and `getLastIterable*Type` instead
* Remove `ConstantArrayType::generalizeToArray()`
* Remove `ConstantArrayType::findTypeAndMethodName()`, use `findTypeAndMethodNames()` instead
* Remove `ConstantArrayType::removeLast()`, use [`Type::popArray()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_popArray) instead
* Remove `ConstantArrayType::removeFirst()`, use [`Type::shiftArray()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_shiftArray) instead
* Remove `ConstantArrayType::reverse()`, use [`Type::reverseArray()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_reverseArray) instead
* Remove `ConstantArrayType::chunk()`, use [`Type::chunkArray()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_chunkArray) instead
* Remove `ConstantArrayType::slice()`, use [`Type::sliceArray()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_sliceArray) instead
* Made `TypeUtils` thinner by removing methods:
  * Remove `TypeUtils::getArrays()` and `getAnyArrays()`, use [`Type::getArrays()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_getArrays) instead
  * Remove `TypeUtils::getConstantArrays()` and `getOldConstantArrays()`, use [`Type::getConstantArrays()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_getConstantArrays) instead
  * Remove `TypeUtils::getConstantStrings()`, use [`Type::getConstantStrings()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_getConstantStrings) instead
  * Remove `TypeUtils::getConstantTypes()` and `getAnyConstantTypes()`, use [`Type::isConstantValue()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_isConstantValue) or [`Type::generalize()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_generalize)
  * Remove `TypeUtils::generalizeType()`, use [`Type::generalize()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_generalize) instead
  * Remove `TypeUtils::getDirectClassNames()`, use [`Type::getObjectClassNames()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_getObjectClassNames) instead
  * Remove `TypeUtils::getConstantScalars()`, use [`Type::isConstantScalarValue()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_isConstantScalarValue) or [`Type::getConstantScalarTypes()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_getConstantScalarTypes) instead
  * Remove `TypeUtils::getEnumCaseObjects()`, use [`Type::getEnumCases()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_getEnumCases) instead
  * Remove `TypeUtils::containsCallable()`, use [`Type::isCallable()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_isCallable) instead
* Removed `Scope::doNotTreatPhpDocTypesAsCertain()`, use `getNativeType()` instead
* Parameter `$isList` in `ConstantArrayType` constructor can only be `TrinaryLogic`, no longer `bool`
* Parameter `$nextAutoIndexes` in `ConstantArrayType` constructor can only be `non-empty-list<int>`, no longer `int`
* Remove `ConstantType` interface, use [`Type::isConstantValue()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_isConstantValue) instead
* `acceptsNamedArguments()` in `FunctionReflection`, `ExtendedMethodReflection` and `CallableParametersAcceptor` interfaces returns `TrinaryLogic` instead of `bool`
* Remove `FunctionReflection::isFinal()`
* [`Type::getProperty()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.Type.html#_getProperty) now returns [`ExtendedPropertyReflection`](https://apiref.phpstan.org/2.0.x/PHPStan.Reflection.ExtendedPropertyReflection.html)
* Remove `__set_state()` on objects that should not be serialized in cache
* Parameter `$selfClass` of [`TypehintHelper::decideTypeFromReflection()`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.TypehintHelper.html#_decideTypeFromReflection) no longer accepts `string`
* `LevelsTestCase::dataTopics()` data provider made static
* `PHPStan\Node\Printer\Printer` no longer autowired as `PhpParser\PrettyPrinter\Standard`, use `PHPStan\Node\Printer\Printer` in the typehint
* Remove `Type::acceptsWithReason()`, `Type:accepts()` return type changed from `TrinaryLogic` to [`AcceptsResult`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.AcceptsResult.html)
* Remove `CompoundType::isAcceptedWithReasonBy()`, `CompoundType::isAcceptedBy()` return type changed from `TrinaryLogic` to [`AcceptsResult`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.AcceptsResult.html)
Remove `Type::isSuperTypeOfWithReason()`, `Type:isSuperTypeOf()` return type changed from `TrinaryLogic` to [`IsSuperTypeOfResult`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.IsSuperTypeOfResult.html)
* Remove `CompoundType::isSubTypeOfWithReasonBy()`, `CompoundType::isSubTypeOf()` return type changed from `TrinaryLogic` to [`IsSuperTypeOfResult`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.IsSuperTypeOfResult.html)
* Remove `TemplateType::isValidVarianceWithReason()`, changed `TemplateType::isValidVariance()` return type to [`IsSuperTypeOfResult`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.IsSuperTypeOfResult.html)
* `RuleLevelHelper::accepts()` return type changed from `bool` to [`RuleLevelHelperAcceptsResult`](https://apiref.phpstan.org/2.0.x/PHPStan.Type.AcceptsResult.html)
* Changes around `ClassConstantReflection`
  * Class `ClassConstantReflection` removed from BC promise, renamed to `RealClassConstantReflection`
  * Interface `ConstantReflection` renamed to `ClassConstantReflection`
  * Added more methods around PHPDoc types and native types to the (new) `ClassConstantReflection`
  * Interface `GlobalConstantReflection` renamed to `ConstantReflection`
* Renamed interfaces and classes from `*WithPhpDocs` to `Extended*`
  * `ParametersAcceptorWithPhpDocs` -> `ExtendedParametersAcceptor`
  * `ParameterReflectionWithPhpDocs` -> `ExtendedParameterReflection`
  * `FunctionVariantWithPhpDocs` -> `ExtendedFunctionVariant`
* `ClassPropertyNode::getNativeType()` return type changed from AST node to `Type|null`
* Class `PHPStan\Node\ClassMethod` (accessible from `ClassMethodsNode`) is no longer an AST node
  * Call `PHPStan\Node\ClassMethod::getNode()` to access the original AST node
