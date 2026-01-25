#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Pekral\BuildPackage\PostCreateProject;
use Symfony\Component\Console\Application;

$application = new Application('PHP Skeleton', '1.0.0');
$application->addCommand(new PostCreateProject());
$application->setDefaultCommand('configure', true);
$application->run();
