<?php

if (!file_exists(__DIR__.'/src')) {
    exit(0);
}

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('tests/tmp')
    ->exclude('fixtures')
    // the PHP template files are a bit special
    ->notName('*.tpl.php')
;

return (new PhpCsFixer\Config())
    ->setRules(array(
        '@PHPUnit75Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'protected_to_private' => false,
        'semicolon_after_instruction' => false,
    ))
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ;
