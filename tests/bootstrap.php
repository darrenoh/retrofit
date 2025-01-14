<?php

declare(strict_types=1);

use Drupal\TestTools\PhpUnitCompatibility\ClassWriter;
use Drupal\TestTools\PhpUnitCompatibility\PhpUnit8\ClassWriter as ClassWriterD9;

$loader = require __DIR__ . '/../vendor/autoload.php';

// Start with classes in known locations.
$loader->add('Drupal\\BuildTests', __DIR__ . '/../vendor/drupal/core/tests');
$loader->add('Drupal\\Tests', __DIR__ . '/../vendor/drupal/core/tests');
$loader->add('Drupal\\TestSite', __DIR__ . '/../vendor/drupal/core/tests');
$loader->add('Drupal\\KernelTests', __DIR__ . '/../vendor/drupal/core/tests');
$loader->add('Drupal\\FunctionalTests', __DIR__ . '/../vendor/drupal/core/tests');
$loader->add('Drupal\\FunctionalJavascriptTests', __DIR__ . '/../vendor/drupal/core/tests');
$loader->add('Drupal\\TestTools', __DIR__ . '/../vendor/drupal/core/tests');
$loader->addPsr4('Drupal\\block\\', __DIR__ . '/../vendor/drupal/core/modules/block/src');
$loader->addPsr4('Drupal\\entity_test\\', __DIR__ . '/../vendor/drupal/core/modules/system/tests/modules/entity_test/src');
$loader->addPsr4('Drupal\\field\\', __DIR__ . '/../vendor/drupal/core/modules/field/src');
$loader->addPsr4('Drupal\\node\\', __DIR__ . '/../vendor/drupal/core/modules/node/src');
$loader->addPsr4('Drupal\\sqlite\\', __DIR__ . '/../vendor/drupal/core/modules/sqlite/src');
$loader->addPsr4('Drupal\\taxonomy\\', __DIR__ . '/../vendor/drupal/core/modules/taxonomy/src');
$loader->addPsr4('Drupal\\user\\', __DIR__ . '/../vendor/drupal/core/modules/user/src');
$loader->addPsr4('Drupal\\Tests\\user\\Traits\\', __DIR__ . '/../vendor/drupal/core/modules/user/tests/src/Traits');

if (class_exists(ClassWriter::class)) {
    ClassWriter::mutateTestBase($loader);
} elseif (class_exists(ClassWriterD9::class)) {
    ClassWriterD9::mutateTestBase($loader);
}

file_put_contents(__DIR__ . '/../vendor/drupal/autoload.php', <<<AUTOLOAD
<?php

/**
 * @file
 * Includes the autoloader created by Composer.
 *
 * This file was generated by drupal-scaffold.
 *
 * @see composer.json
 * @see index.php
 * @see core/install.php
 * @see core/rebuild.php
 * @see core/modules/statistics/statistics.php
 */

return require __DIR__ . '/../autoload.php';

AUTOLOAD);
