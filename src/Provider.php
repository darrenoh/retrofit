<?php

declare(strict_types=1);

namespace Retrofit\Drupal;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Template\Loader\FilesystemLoader;
use Retrofit\Drupal\Asset\RetrofitJsCollectionRenderer;
use Retrofit\Drupal\Asset\RetrofitLibraryDiscovery;
use Retrofit\Drupal\Controller\RetrofitTitleResolver;
use Retrofit\Drupal\Entity\EntityTypeManager;
use Retrofit\Drupal\Field\FieldTypePluginManager;
use Retrofit\Drupal\Form\FormBuilder;
use Retrofit\Drupal\Language\GlobalLanguageSetter;
use Retrofit\Drupal\Menu\LocalActionManager;
use Retrofit\Drupal\Menu\LocalTaskManager;
use Retrofit\Drupal\Menu\MenuLinkManager;
use Retrofit\Drupal\Extension\ModuleHandler;
use Retrofit\Drupal\ParamConverter\PageArgumentsConverter;
use Retrofit\Drupal\Path\CurrentPathStack;
use Retrofit\Drupal\Render\AttachmentResponseSubscriber;
use Retrofit\Drupal\Render\RetrofitHtmlResponseAttachmentsProcessor;
use Retrofit\Drupal\Routing\HookMenuRegistry;
use Retrofit\Drupal\Routing\HookMenuRoutes;
use Retrofit\Drupal\Template\RetrofitExtension;
use Retrofit\Drupal\Theme\Registry;
use Retrofit\Drupal\User\GlobalUserSetter;
use Retrofit\Drupal\User\HookPermissions;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Reference;

class Provider extends ServiceProviderBase
{
    public function register(ContainerBuilder $container)
    {
        $namespaces = $container->getParameter('container.namespaces');
        $namespaces['Retrofit\Drupal'] = __DIR__;
        $container->setParameter('container.namespaces', $namespaces);

        $container
          ->register(HookMenuRegistry::class)
          ->addArgument(new Reference('module_handler'))
          ->addArgument(new Reference('cache.data'));

        $container
          ->register(HookMenuRoutes::class)
          ->addArgument(new Reference('module_handler'))
          ->setAutowired(true)
          ->addTag('event_subscriber');

        $container
          ->register(GlobalUserSetter::class)
          ->addTag('event_subscriber');

        $container
          ->register(GlobalLanguageSetter::class)
          ->addArgument(new Reference('language_manager'))
          ->addArgument(new Reference('module_handler'))
          ->addTag('event_subscriber');

        $container
          ->register(PageArgumentsConverter::class)
          ->addTag('paramconverter');

        $container->setDefinition(
            ModuleHandler::class,
            (new ChildDefinition('module_handler'))
            ->setDecoratedService('module_handler')
        );

        $container->setDefinition(
            MenuLinkManager::class,
            (new ChildDefinition('plugin.manager.menu.link'))
            ->setDecoratedService('plugin.manager.menu.link')
        );

        $container->setDefinition(
            LocalActionManager::class,
            (new ChildDefinition('plugin.manager.menu.local_action'))
            ->setDecoratedService('plugin.manager.menu.local_action')
        );

        $container->setDefinition(
            LocalTaskManager::class,
            (new ChildDefinition('plugin.manager.menu.local_task'))
            ->setDecoratedService('plugin.manager.menu.local_task')
        );

        $container->setDefinition(
            Registry::class,
            (new ChildDefinition('theme.registry'))
            ->setDecoratedService('theme.registry')
        );

        $container->setDefinition(
            FilesystemLoader::class,
            (new ChildDefinition('twig.loader.filesystem'))
            ->setDecoratedService('twig.loader.filesystem')
            ->addMethodCall('addPath', [__DIR__ . '/../templates', 'retrofit'])
        );

        $container->register(RetrofitExtension::class)
            ->addArgument(new Reference('theme.registry'))
            ->addTag('twig.extension');

        if ($container->has('user.permissions')) {
            $container
              ->register(HookPermissions::class)
              ->setDecoratedService('user.permissions')
              ->addArgument(new Reference(HookPermissions::class . '.inner'))
              ->addArgument(new Reference('module_handler'));
        }

        $container->register(RetrofitHtmlResponseAttachmentsProcessor::class)
            ->setDecoratedService('html_response.attachments_processor')
            ->addArgument(new Reference(RetrofitHtmlResponseAttachmentsProcessor::class . '.inner'))
            ->addArgument(new Reference('asset.js.collection_renderer'))
            ->setAutowired(true);

        $container->register(AttachmentResponseSubscriber::class)
            ->addTag('event_subscriber');

        $container->register(RetrofitLibraryDiscovery::class)
            ->setDecoratedService('library.discovery')
            ->setAutowired(true);

        $container->register(RetrofitJsCollectionRenderer::class)
            ->setDecoratedService('asset.js.collection_renderer')
            ->setAutowired(true);

        $container->setDefinition(
            FieldTypePluginManager::class,
            (new ChildDefinition('plugin.manager.field.field_type'))
            ->setDecoratedService('plugin.manager.field.field_type')
        );

        $container->register(RetrofitTitleResolver::class)
            ->setDecoratedService('title_resolver')
            ->addArgument(new Reference(RetrofitTitleResolver::class . '.inner'))
            ->addArgument(new Reference('request_stack'));

        $container->setDefinition(
            FormBuilder::class,
            (new ChildDefinition('form_builder'))
            ->setDecoratedService('form_builder')
        );

        $container->setDefinition(
            EntityTypeManager::class,
            (new ChildDefinition('entity_type.manager'))
            ->setDecoratedService('entity_type.manager')
        );

        $container->setDefinition(
            CurrentPathStack::class,
            (new ChildDefinition('path.current'))
            ->setDecoratedService('path.current')
        );
    }

    public function alter(ContainerBuilder $container)
    {
    }
}
