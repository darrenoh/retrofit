<?php

declare(strict_types=1);

use Drupal\Component\Utility\Xss;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\FieldConfigInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldStorageConfigInterface;

function _field_sort_items_value_helper(mixed $a, mixed $b): float
{
    return _field_multiple_value_form_sort_helper($a, $b);
}

/**
 * @param array{
 *   field_name: string,
 *   type: string
 * } $field
 */
function field_create_field(array $field): FieldStorageConfigInterface
{
    $info = drupal_static('retrofit_field_info');
    if (!isset($info)) {
        $info = [];
        \Drupal::moduleHandler()->invokeAllWith(
            'field_info',
            function (callable $hook, string $module) use (&$info): void {
                $info += $hook();
            }
        );
    }
    assert(is_array($info));
    if (isset($info[$field['type']])) {
        $field['type'] = "retrofit_field:$field[type]";
    }
    $field_storage = FieldStorageConfig::create($field + ['entity_type' => 'node']);
    $field_storage->save();
    return $field_storage;
}

/**
 * @param array{
 *   field_name: string,
 *   entity_type: string,
 *   bundle: string
 * } $instance
 */
function field_create_instance(array $instance): FieldConfigInterface
{
    $field = FieldConfig::create($instance);
    $field->save();
    return $field;
}

function field_filter_xss(string $string): string
{
    return Xss::filter($string, FieldFilteredMarkup::allowedTags());
}

/**
 * @param array<string, int> $parents
 * @return mixed[]
 */
function field_form_get_state(
    array $parents,
    string $field_name,
    string $langcode,
    FormStateInterface $form_state
): array {
    return WidgetBase::getWidgetState($parents, $field_name, $form_state);
}

/**
 * @param array<string, int> $parents
 * @param mixed[] $field_state
 */
function field_form_set_state(
    array $parents,
    string $field_name,
    string $langcode,
    FormStateInterface $form_state,
    array $field_state
): void {
    WidgetBase::setWidgetState($parents, $field_name, $form_state, $field_state);
}
