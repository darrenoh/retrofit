<?php

declare(strict_types=1);

function drupal_set_message(null|string|Stringable $message = null, string $type = 'status', bool $repeat = true)
{
    $messenger = \Drupal::messenger();
    if (isset($message)) {
        $messenger->addMessage((string) $message, $type, $repeat);
    }
    return $messenger->all();
}
