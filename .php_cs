<?php
$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->files()
    ->in('build')
    ->in('src')
    ->in('tests')
    ->name('*.php')
    ->name('*.phpt');

return Symfony\CS\Config\Config::create()
    ->level(\Symfony\CS\FixerInterface::NONE_LEVEL)
    ->fixers(
        array(
            'duplicate_semicolon',
            'empty_return',
            'extra_empty_lines',
            'join_function',
            'list_commas',
            'no_blank_lines_after_class_opening',
            'no_empty_lines_after_phpdocs',
            'phpdoc_indent',
            'phpdoc_no_access',
            'phpdoc_no_empty_return',
            'phpdoc_no_package',
            'phpdoc_params',
            'phpdoc_scalar',
            'phpdoc_to_comment',
            'phpdoc_trim',
            'return',
            'self_accessor',
            'single_quote',
            'spaces_before_semicolon',
            'spaces_cast',
            'ternary_spaces',
            'trim_array_spaces',
            'unused_use',
            'whitespacy_lines',
            'align_double_arrow',
            'align_equals',
            'concat_with_spaces'
        )
    )
    ->finder($finder);

