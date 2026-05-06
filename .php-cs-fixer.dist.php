<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => ['default' => 'single_space'],
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw'],
        ],
        'braces_position' => [
            'classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'anonymous_classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
        ],
        'cast_spaces' => ['space' => 'single'],
        'class_definition' => false,
        'concat_space' => ['spacing' => 'one'],
        'declare_parentheses' => true,
        'lowercase_keywords' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'no_extra_blank_lines' => true,
        'no_trailing_whitespace' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_blank_line_at_eof' => true,
        'single_quote' => true,
    ])
    ->setFinder($finder);
