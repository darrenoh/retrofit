<?php

declare(strict_types=1);

namespace Retrofit\Drupal\Entity;

class EntityTypeManager extends \Drupal\Core\Entity\EntityTypeManager
{
  protected function findDefinitions()
  {
      $definitions = $this->getDiscovery()->getDefinitions();
      if (isset($definitions['user_role'])) {
          $definitions['user_role']->setClass(Role::class);
      }
      $this->moduleHandler->invokeAllWith('entity_type_build', function (
          callable $hook,
          string $module
      ) use (&$definitions) {
          $hook($definitions);
      });
      foreach ($definitions as $plugin_id => $definition) {
          $this->processDefinition($definition, $plugin_id);
      }
      $this->alterDefinitions($definitions);

      return $definitions;
  }
}
