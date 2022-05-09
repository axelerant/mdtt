<?php

$finder = PhpCsFixer\Finder::create()
  ->in("src")
  ->in("tests")
;

$config = new PhpCsFixer\Config();
return $config->setRules([
  '@PSR2' => true,
])
  ->setFinder($finder)
  ;
