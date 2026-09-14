<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Phenix\Core\Application;

$app = new Application();

echo $app->name() . PHP_EOL;