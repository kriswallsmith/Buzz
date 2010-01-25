<?php

require __DIR__.'/../bootstrap/unit.php';

$suite = new LimeTestSuite();
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../unit'), RecursiveIteratorIterator::LEAVES_ONLY) as $file)
{
  if (preg_match('/Test\.php$/', $file))
  {
    $suite->register($file->getRealPath());
  }
}

exit($suite->run() ? 0 : 1);
