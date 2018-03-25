<?php

return PhpCsFixer\Config::create()
    ->setRules(array(
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => array('syntax' => 'short'),
        'protected_to_private' => false,
        'declare_strict_types' => true,
    ))
    ->setRiskyAllowed(true)
    ->setCacheFile((getenv('TRAVIS') ? getenv('HOME') . '/.php-cs-fixer' : __DIR__).'/.php_cs.cache')
    ->setUsingCache(true)
;