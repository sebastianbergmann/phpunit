# CHANGELOG for PHP CS Fixer: custom fixers

## v3.27.0
- Add PhpdocTagNoNamedArgumentsFixer

## v3.26.0
- Add TypedClassConstantFixer

## v3.25.0
- Add ForeachUseValueFixer
- Add NoUselessWriteVisibilityFixer
- Add TrimKeyFixer
- ReadonlyPromotedPropertiesFixer - support asymmetric visibility

## v3.24.0
- Add PhpUnitRequiresConstraintFixer

## v3.23.0
- Add ClassConstantUsageFixer

## v3.22.0
- NoSuperfluousConcatenationFixer - add option "keep_concatenation_for_different_quotes"
- NoPhpStormGeneratedCommentFixer - handle more comments
- Update minimum PHP CS Fixer version to 3.61.1

## v3.21.0
- Deprecate PhpdocArrayStyleFixer - use "phpdoc_array_type"
- NoUselessParenthesisFixer - keep parentheses around `and`, `xor` and `or`
- Update minimum PHP CS Fixer version to 3.50.0

## v3.20.0
- Deprecate PhpdocTypeListFixer - use "phpdoc_list_type"
- Update minimum PHP CS Fixer version to 3.49.0

## v3.19.0
- Deprecate NumericLiteralSeparatorFixer - use "numeric_literal_separator"
- Update minimum PHP CS Fixer version to 3.47.0

## v3.18.0
- Add PhpdocTypeListFixer

## v3.17.0
- PhpdocNoIncorrectVarAnnotationFixer - support promoted properties

## v3.16.0
- Deprecate DataProviderReturnTypeFixer - use "php_unit_data_provider_return_type"
- Update minimum PHP CS Fixer version to 3.22.0

## v3.15.0
- Deprecate DataProviderNameFixer - use "php_unit_data_provider_name"
- Deprecate PhpdocParamOrderFixer - use "phpdoc_param_order"
- Update minimum PHP CS Fixer version to 3.19.0

## v3.14.0
- Add EmptyFunctionBodyFixer
- Deprecate DataProviderStaticFixer - use "php_unit_data_provider_static"
- Update minimum PHP CS Fixer version to 3.16.0

## v3.13.0
- MultilinePromotedPropertiesFixer - add option "keep_blank_lines"

## v3.12.0
- MultilinePromotedPropertiesFixer - add option "minimum_number_of_parameters"
- Add `bootstrap.php`

## v3.11.0
- Add ReadonlyPromotedPropertiesFixer

## v3.10.0
- Do not require `friendsofphp/php-cs-fixer` as dependency (to allow using `php-cs-fixer/shim`)

## v3.9.0
- Add PhpdocTypesCommaSpacesFixer

## v3.8.0
- Update minimum PHP version to 7.4
- Update minimum PHP CS Fixer version to 3.6.0
- DataProviderStaticFixer - add option "force"
- Deprecate InternalClassCasingFixer

## v3.7.0
- Add NoTrailingCommaInSinglelineFixer

## v3.6.0
- Add IssetToArrayKeyExistsFixer
- Add PhpdocVarAnnotationToAssertFixer

## v3.5.0
- Add NoUselessDirnameCallFixer

## v3.4.0
- Add DeclareAfterOpeningTagFixer

## v3.3.0
- Add ConstructorEmptyBracesFixer
- PhpdocNoIncorrectVarAnnotationFixer - remove more incorrect annotations

## v3.2.0
- Add PhpUnitAssertArgumentsOrderFixer
- Add PhpUnitDedicatedAssertFixer
- PromotedConstructorPropertyFixer - add option "promote_only_existing_properties"
- NoUselessCommentFixer - support PHPDoc like `/** ClassName */`

## v3.1.0
- Add MultilinePromotedPropertiesFixer
- Add PhpdocArrayStyleFixer
- Add PromotedConstructorPropertyFixer
- Restore PhpCsFixerCustomFixers\Analyzer\SwitchAnalyzer (as PhpCsFixer\Tokenizer\Analyzer\SwitchAnalyzer got removed in PHP CS Fixer 3.2.0)

## v3.0.0
- Drop support for PHP CS Fixer v2
- Add StringableInterfaceFixer
- Remove NoUselessSprintfFixer - use "no_useless_sprintf"
- Remove OperatorLinebreakFixer - use "operator_linebreak"
- NoCommentedOutCodeFixer - do not remove URLs
- NoDuplicatedArrayKeyFixer - add option "ignore_expressions"
- NoUselessParenthesisFixer - fix expressions
- PhpdocNoIncorrectVarAnnotationFixer - handle class properties when variable names are different and constants with visibility

## v2.5.0
- Add PHP CS Fixer v3 support

## v2.4.0
- Allow PHP 8
- Update PHP CS Fixer to v2.17
- Deprecate NoUselessSprintfFixer - use "no_useless_sprintf"
- Deprecate OperatorLinebreakFixer - use "operator_linebreak"
- Remove PhpCsFixerCustomFixers\Analyzer\ReferenceAnalyzer - use PhpCsFixer\Tokenizer\Analyzer\ReferenceAnalyzer
- Remove PhpCsFixerCustomFixers\Analyzer\SwitchAnalyzer - use PhpCsFixer\Tokenizer\Analyzer\SwitchAnalyzer

## v2.3.0
- Add NoUselessParenthesisFixer
- Add NoUselessStrlenFixer
- DataProviderNameFixer - handle snake_case naming

## v2.2.0
- Feature: DataProviderNameFixer - add options "prefix" and "suffix"

## v2.1.0
- Add CommentedOutFunctionFixer
- Add NoDuplicatedArrayKeyFixer
- Add NumericLiteralSeparatorFixer

## v2.0.0
- Drop PHP 7.1 support
- Remove ImplodeCallFixer - use "implode_call"
- Remove NoTwoConsecutiveEmptyLinesFixer - use "no_extra_blank_lines"
- Remove NoUnneededConcatenationFixer - use NoSuperfluousConcatenationFixer
- Remove NoUselessClassCommentFixer - use NoUselessCommentFixer
- Remove NoUselessConstructorCommentFixer - use NoUselessCommentFixer
- Remove NullableParamStyleFixer - use "nullable_type_declaration_for_default_null_value"
- Remove PhpdocVarAnnotationCorrectOrderFixer - use "phpdoc_var_annotation_correct_order"
- Remove SingleLineThrowFixer - use "single_line_throw"

## v1.17.0
- Update PHP CS Fixer to v2.16
- Add DataProviderStaticFixer
- Add NoSuperfluousConcatenationFixer
- Add PhpdocTypesTrimFixer
- Feature: NoSuperfluousConcatenationFixer - add option "allow_preventing_trailing_spaces"
- Feature: NoSuperfluousConcatenationFixer - handle concatenation of single and double quoted strings together
- Deprecate NoUnneededConcatenationFixer
- Deprecate NullableParamStyleFixer
- Deprecate SingleLineThrowFixer
- Allow symfony/finder 5.0
- Add Windows OS support with AppVeyor

## v1.16.0
- Add PhpdocOnlyAllowedAnnotationsFixer
- Feature: OperatorLinebreakFixer - handle object operators

## v1.15.0
- Add CommentSurroundedBySpacesFixer
- Add DataProviderReturnTypeFixer
- Add NoDuplicatedImportsFixer

## v1.14.0
- Add DataProviderNameFixer
- Add NoUselessSprintfFixer
- Add PhpUnitNoUselessReturnFixer
- Add SingleLineThrowFixer
- Feature: NoCommentedOutCodeFixer - handle class method

## v1.13.0
- Update PHP CS Fixer to v2.14
- OperatorLinebreakFixer - respect no whitespace around operator
- OperatorLinebreakFixer - support concatenation operator
- Deprecate PhpdocVarAnnotationCorrectOrderFixer

## v1.12.0
- Add NoCommentedOutCodeFixer
- Add NoUselessCommentFixer
- Add NullableParamStyleFixer
- Deprecate NoUselessClassCommentFixer
- Deprecate NoUselessConstructorCommentFixer
- Feature: OperatorLinebreakFixer - handle ternary operator
- Fix: NoImportFromGlobalNamespaceFixer - class without namespace
- Fix: NoUselessClassCommentFixer - comment detection
- Fix: TokenRemover - remove last element of file
- Fix: TokenRemover - remove item in line after code
- Fix: NoImportFromGlobalNamespaceFixer - constant named the same as global imported class

## v1.11.0
- Add PhpdocParamOrderFixer
- Add InternalClassCasingFixer
- Add SingleSpaceAfterStatementFixer
- Add SingleSpaceBeforeStatementFixer
- Add OperatorLinebreakFixer
- Add MultilineCommentOpeningClosingAloneFixer

## v1.10.0
- Add NoUnneededConcatenationFixer
- Add PhpdocNoSuperfluousParamFixer
- Deprecate ImplodeCallFixer
- Deprecate NoTwoConsecutiveEmptyLinesFixer

## v1.9.0
- Add NoNullableBooleanTypeFixer

## v1.8.0
- Add PhpdocSelfAccessorFixer

## v1.7.0
- Add NoReferenceInFunctionDefinitionFixer
- Add NoImportFromGlobalNamespaceFixer

## v1.6.0
- Add ImplodeCallFixer
- Add PhpdocSingleLineVarFixer

## v1.5.0
- Add NoUselessDoctrineRepositoryCommentFixer

## v1.4.0
- Add NoDoctrineMigrationsGeneratedCommentFixer

## v1.3.0
- Add PhpdocVarAnnotationCorrectOrderFixer
- Remove @var without type at the beginning in PhpdocNoIncorrectVarAnnotationFixer

## v1.2.0
- Add PhpdocNoIncorrectVarAnnotationFixer

## v1.1.0
- Update PHP CS Fixer to v2.12
- Add NoUselessConstructorCommentFixer
- Add PhpdocParamTypeFixer
- Feature: code coverage
- Feature: create Travis stages
- Feature: verify correctness for PHP CS Fixer (without smote tests)
- Fix: false positive class comment

## v1.0.0
- Add NoLeadingSlashInGlobalNamespaceFixer
- Add NoPhpStormGeneratedCommentFixer
- Add NoTwoConsecutiveEmptyLinesFixer
- Add NoUselessClassCommentFixer
