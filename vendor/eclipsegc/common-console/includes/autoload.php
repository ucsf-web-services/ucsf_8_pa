<?php

// Autoload mechanism stolen from Drush. Clearly they had the same problem.
$cwd = isset($_SERVER['PWD']) && is_dir($_SERVER['PWD']) ? $_SERVER['PWD'] : getcwd();

if (file_exists($autoloadFile = __DIR__ . '/../vendor/autoload.php')
  || file_exists($autoloadFile = __DIR__ . '/../../autoload.php')
  || file_exists($autoloadFile = __DIR__ . '/../../../autoload.php')
) {
  $loader = require $autoloadFile;
} else {
  throw new \Exception("Could not locate autoload.php. cwd is $cwd; __DIR__ is " . __DIR__);
}
return [$loader, $autoloadFile];
