<?php

declare(strict_types=1);

namespace Retrofit\Drupal\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class FieldTypeDeriver extends DeriverBase implements ContainerDeriverInterface
{
    public function __construct(
        private readonly ModuleHandlerInterface $moduleHandler
    ) {
    }

    public static function create(ContainerInterface $container, $base_plugin_id)
    {
        return new self(
            $container->get('module_handler')
        );
    }

    public function getDerivativeDefinitions($base_plugin_definition)
    {
        $this->moduleHandler->invokeAllWith(
            'field_info',
            function (callable $hook, string $module) use ($base_plugin_definition) {
                $definitions = $hook();
                foreach ($definitions as $id => $definition) {
                    $derivative = $base_plugin_definition;
                    $derivative['label'] = $derivative['label'] ?? '';
                    $derivative['description'] = $derivative['description'] ?? '';
                    $derivative['default_widget'] = $derivative['default_widget'] ?? '';
                    $derivative['default_formatter'] = $derivative['default_formatter'] ?? '';
                    $derivative['no_ui'] = $derivative['no_ui'] ?? false;
                    $derivative['cardinality'] = $derivative['cardinality'] ?? null;
                    $derivative['provider'] = $module;
                    $derivative['field_info'] = $definition;
                    $this->derivatives[$id] = $derivative;
                }
            }
        );
        return $this->derivatives;
    }
}
