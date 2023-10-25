<?php

declare(strict_types=1);

use Drupal\Core\Form\FormStateInterface;
use Retrofit\Drupal\Form\DrupalGetForm;
use Retrofit\Drupal\Form\ArrayAccessFormState;

/**
 * @param mixed[] $form_state
 * @return mixed[]
 */
function drupal_build_form(string $form_id, array &$form_state): array
{
    $form_object = \Drupal::classResolver(DrupalGetForm::class);
    $form_object->setFormId($form_id);
    $original_form_state = $form_state;
    $form_state = new ArrayAccessFormState();
    foreach ($original_form_state as $offset => $value) {
        $form_state[$offset] = $value;
    }
    return \Drupal::formBuilder()->buildForm($form_object, $form_state);
}

/**
 * @return mixed[]
 */
function drupal_get_form(string $form_id): array
{
    $form_object = \Drupal::classResolver(DrupalGetForm::class);
    $form_object->setFormId($form_id);
    return \Drupal::formBuilder()->getForm($form_object);
}

function form_load_include(
    FormStateInterface &$form_state,
    string $type,
    string $module,
    ?string $name = null
): string|false {
    return $form_state->loadInclude($module, $type, $name);
}

function form_set_error($name = null, $message = '', $limit_validation_errors = null)
{
    $form = &drupal_static(__FUNCTION__, array());
    $sections = &drupal_static(__FUNCTION__ . ':limit_validation_errors');
    if (isset($limit_validation_errors)) {
        $sections = $limit_validation_errors;
    }
    if (isset($name) && !isset($form[$name])) {
        $record = true;
        if (isset($sections)) {
            $record = false;
            foreach ($sections as $section) {
                if (array_slice(explode('][', $name), 0, count($section)) === array_map('strval', $section)) {
                    $record = true;
                    break;
                }
            }
        }
        if ($record) {
            $form[$name] = $message;
        }
    }

    return $form;
}

function form_get_errors()
{
    $form = form_set_error();
    if (!empty($form)) {
        return $form;
    }
}
