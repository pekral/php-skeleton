#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Post-create-project script for PHP Skeleton
 *
 * This script runs after `composer create-project pekral/php-skeleton` and:
 * 1. Asks for new package name, namespace, and display name
 * 2. Replaces all occurrences in relevant files
 * 3. Updates PSR-4 autoload configuration
 * 4. Deletes example files
 * 5. Removes .git directory
 * 6. Removes build-package directory
 * 7. Optionally initializes a new git repository
 */

require_once __DIR__ . '/vendor/autoload.php';

use Pekral\BuildPackage\PostCreateProject;

$script = new PostCreateProject();
$script->run();
