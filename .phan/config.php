<?php declare(strict_types=1);
return [
    'directory_list'                  => [ 'src/', 'vendor/' ],
    'exclude_analysis_directory_list' => [ 'vendor/', 'src/Framework/Assert/' ],

    'target_php_version' => '7.2',

    'simplify_ast' => true,

    'analyze_signature_compatibility'  => true,
    'allow_method_param_type_widening' => true,

    'strict_method_checking'   => true,
    'strict_param_checking'    => true,
    'strict_property_checking' => true,
    'strict_return_checking'   => true,

    'allow_missing_properties'  => false,
    'unused_variable_detection' => true,

    'warn_about_undocumented_throw_statements'                       => true,
    'warn_about_undocumented_exceptions_thrown_by_invoked_functions' => true,

    'suppress_issue_types' => [
        'PhanAccessClassInternal',
        'PhanAccessClassConstantInternal',
        'PhanAccessMethodInternal',
        'PhanAccessPropertyInternal',
        'PhanDeprecatedFunction',
    ],
];
