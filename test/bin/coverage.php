<?php

require __DIR__.'/../bootstrap/unit.php';

$suite = new LimeTestSuite(array(
  'base_dir' => realpath(__DIR__.'/../../lib'),
  'verbose'  => true,
));
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../unit'), RecursiveIteratorIterator::LEAVES_ONLY) as $file)
{
  if (preg_match('/Test\.php$/', $file))
  {
    $suite->register($file->getRealPath());
  }
}

$coverage = new LimeCoverage($suite, array(
  'base_dir' => realpath(__DIR__.'/../../lib'),
  'verbose'  => true,
));
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../../lib'), RecursiveIteratorIterator::LEAVES_ONLY) as $file)
{
  if (preg_match('/\.php$/', $file))
  {
    $coverage->register($file->getRealPath());
  }
}

$coverage->run();
