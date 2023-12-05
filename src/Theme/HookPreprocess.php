<?php

declare(strict_types=1);

namespace Retrofit\Drupal\Theme;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\node\NodeInterface;
use Retrofit\Drupal\Entity\WrappedConfigEntity;

/**
 * @phpstan-type Variables array<string, string|array<int|string, mixed>>
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
final class HookPreprocess
{
    /**
     * @param Variables $variables
     */
    public static function page(array &$variables): void
    {
        $variables['logo'] = theme_get_setting('logo.url');

        // @todo support in https://github.com/retrofit-drupal/retrofit/issues/43
        $variables['main_menu'] = [];
        $variables['secondary_menu'] = [];

        // Legacy variables replaced by blocks.
        $variables['title'] = $variables['page']['#title'] ?? '';
        $variables['breadcrumb'] = '';
        $variables['messages'] = '';
        $variables['tabs'] = '';
        $variables['action_links'] = '';
        $variables['feed_icons'] = '';
    }

    /**
     * @param Variables $variables
     */
    public static function maintenance_page(array &$variables): void
    {
        self::page($variables);
    }

    /**
     * @param Variables $variables
     */
    public static function block(array &$variables): void
    {
        // @todo find a way to do this earlier.
        // \Drupal\block\BlockViewBuilder::preRender removes the block
        // after building the plugin. This is within a lazy builder
        // that is called through a class name and method callable,
        // not a service.
        $block = \Drupal::entityTypeManager()
            ->getStorage('block')
            ->load($variables['elements']['#id']);
        $variables['block'] = new WrappedConfigEntity($block);
    }

    /**
     * @param array{
     *   node: NodeInterface<string, FieldItemListInterface>,
     *   teaser: bool,
     *   url: string,
     *   author_name?: MarkupInterface,
     *   author_picture?: mixed[],
     *   date?: MarkupInterface,
     *   display_submitted?: bool,
     *   label?: mixed[]
     * } $variables
     */
    public static function node(array &$variables): void
    {
        $node = $variables['node'];
        $variables['promote'] = $node->isPromoted();
        $variables['sticky'] = $node->isSticky();
        $variables['status'] = $node->isPublished();
        $variables['preview'] = $node->in_preview ?? null;
        $variables['name'] = $variables['author_name'] ?? '';
        $variables['node_url'] = $variables['url'];
        $variables['title'] = $variables['label'] ?? '';
        $variables['submitted'] = '';
        $variables['user_picture'] = '';
        if (!empty($variables['display_submitted'])) {
            if (isset($variables['date'])) {
                $variables['submitted'] = t('Submitted by @username on @datetime', [
                    '@username' => $variables['name'],
                    '@datetime' => $variables['date'],
                ]);
            }
            if (isset($variables['author_picture'])) {
                $variables['user_picture'] = render($variables['author_picture']);
            }
        }
        $variables['classes_array'][] = drupal_html_class('node-' . $node->bundle());
        if ($variables['promote']) {
            $variables['classes_array'][] = 'node-promoted';
        }
        if ($variables['sticky']) {
            $variables['classes_array'][] = 'node-sticky';
        }
        if (!$variables['status']) {
            $variables['classes_array'][] = 'node-unpublished';
        }
        if ($variables['teaser']) {
            $variables['classes_array'][] = 'node-teaser';
        }
        if (isset($variables['preview'])) {
            $variables['classes_array'][] = 'node-preview';
        }
        $variables['theme_hook_suggestions'][] = 'node__' . $node->bundle();
        $variables['theme_hook_suggestions'][] = 'node__' . $node->id();
    }
}
