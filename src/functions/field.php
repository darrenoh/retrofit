<?php

declare(strict_types=1);

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Field\FieldFilteredMarkup;

function _field_sort_items_value_helper(mixed $a, mixed $b): float
{
    return _field_multiple_value_form_sort_helper($a, $b);
}

function field_filter_xss(string $string): string
{
    return Xss::filter($string, FieldFilteredMarkup::allowedTags());
}

function field_form_get_state(array $parents, string $field_name, string $langcode, array &$form_state): ?array
{
    return NestedArray::getValue($form_state, array_merge([
        'storage',
        'field_storage',
        '#parents',
    ], $parents, [
        '#fields',
        $field_name,
    ]));
}
