<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/lib')
    ->in(__DIR__ . '/tests')
    ->exclude('vendor')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => array('syntax' => 'short'),
        'protected_to_private' => false,
        'declare_strict_types' => true,
        'no_superfluous_phpdoc_tags' => true,
        'nullable_type_declaration_for_default_null_value' => false,
        'modernize_strpos' => false,
    ])
    ->setFinder($finder);
