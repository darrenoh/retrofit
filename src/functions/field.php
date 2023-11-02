<?php

declare(strict_types=1);

use Drupal\Component\Utility\Xss;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

function _field_sort_items_value_helper(mixed $a, mixed $b): float
{
    return _field_multiple_value_form_sort_helper($a, $b);
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
