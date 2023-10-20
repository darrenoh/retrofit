<?php

declare(strict_types=1);

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Extension\ExtensionPathResolver;

function check_plain(MarkupInterface|\Stringable|string $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function drupal_get_filename(string $type, string $name, ?string $filename = null, bool $trigger_error = false): ?string
{
    $pathResolver = \Drupal::service('extension.path.resolver');
    assert($pathResolver instanceof ExtensionPathResolver);
    return $pathResolver->getPathname($type, $name);
}

/**
 * @return mixed[]|bool
 */
function drupal_get_schema(?string $table = null, ?bool $rebuild = false): array|bool
{
    $schema = &drupal_static(__FUNCTION__);
    if (!isset($schema) || $rebuild) {
        if (!$rebuild && ($cached = \Drupal::cache()->get(__FUNCTION__))) {
            $schema = $cached->data;
        } else {
            $schema = [];
            $module_handler = \Drupal::moduleHandler();
            foreach ($module_handler->getModuleList() as $name => $module) {
                if ($module_handler->loadInclude($name, 'install')) {
                    foreach ($module_handler->invoke($name, 'schema') ?? [] as $table_name => $table_schema) {
                        if (empty($table_schema['module'])) {
                            $table_schema['module'] = $name;
                        }
                        if (empty($table_schema['name'])) {
                            $table_schema['name'] = $table_name;
                        }
                        $schema[$table_name] = $table_schema;
                    }
                }
            }
            \Drupal::cache()->set(__FUNCTION__, $schema);
        }
    }
    if (!isset($table)) {
        return $schema;
    }
    if (isset($schema[$table])) {
        return $schema[$table];
    } else {
        return false;
    }
}

function get_t(): string
{
    return 't';
}
