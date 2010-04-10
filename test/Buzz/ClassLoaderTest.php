<?php

namespace Buzz;

include __DIR__.'/../../bootstrap/unit.php';

$t = new \LimeTest(2);

// ->autoload()
$t->diag('->autoload()');

$loader = ClassLoader::getInstance();
$t->is($loader->autoload('Buzz\Browser'), true, '->autoload() returns true if the class exists');
$t->is($loader->autoload('Buzz\Invalid'), false, '->autoload() returns false if the class does not exist');
