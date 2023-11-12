<?php

declare(strict_types=1);

namespace Retrofit\Drupal\Routing;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Retrofit\Drupal\ParamConverter\PageArgumentsConverter;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class HookMenuRoutes extends RouteSubscriberBase
{
    public function __construct(
        private readonly ModuleHandlerInterface $moduleHandler,
        private readonly HookMenuRegistry $hookMenuRegistry
    ) {
    }

    protected function alterRoutes(RouteCollection $collection)
    {
        // @todo needs to process menu_alter
        foreach ($this->hookMenuRegistry->get() as $module => $routes) {
            foreach ($routes as $path => $definition) {
                // May be MENU_DEFAULT_LOCAL_TASK.
                $pageCallback = $definition['page callback'] ?? '';
                if ($pageCallback === '') {
                    continue;
                }
                $collection->add($definition['route_name'], $this->convertToRoute($module, $path, $definition));
            }
        }
    }

    /**
     * @param array{
     *   'page callback': string|string[],
     *   'page arguments'?: array<int|string>,
     *   'load arguments'?: array<int|string>,
     *   'title callback'?: string|string[],
     *   'title arguments'?: array<int|string>,
     *   'access callback'?: string|string[]|bool,
     *   'access arguments'?: array<int|string>,
     *   file?: string,
     *   'file path'?: string,
     *   title?: string
     * } $definition
     */
    private function convertToRoute(string $module, string $path, array $definition): Route
    {
        $pageArguments = $definition['page arguments'] ?? [];
        $parameters = [];
        $pathParts = [];
        foreach (explode('/', $path) as $key => $item) {
            if (!str_starts_with($item, '%')) {
                $pathParts[] = $item;
            } else {
                $placeholder = substr($item, 1);
                if ($placeholder === '') {
                    $placeholder = "arg$key";
                }
                $parameters[$placeholder] = [
                  'converter' => PageArgumentsConverter::class,
                  'load arguments' => $definition['load arguments'] ?? [],
                  'index' => $key,
                ];
                $pathParts[] = '{' . $placeholder . '}';
            }
        }
        foreach ($pageArguments as &$pageArgument) {
            if (is_int($pageArgument)) {
                $pageArgument = $pathParts[$pageArgument];
            }
        }
        if (isset($definition['file'])) {
            $definition['file path'] = $definition['file path'] ?? $this->moduleHandler->getModule($module)->getPath();
            @include_once $definition['file path'] . '/' . $definition['file'];
        }
        $defaults = [];
        if (is_callable($definition['page callback'])) {
            $pageCallback = match (true) {
                is_array($definition['page callback']) => new \ReflectionMethod(...$definition['page callback']),
                strpos($definition['page callback'], '::') !== false => new \ReflectionMethod(
                    ...explode('::', $definition['page callback'], 2)
                ),
                default => new \ReflectionFunction($definition['page callback']),
            };
            $paramCount = $pageCallback->getNumberOfParameters();
            if ($paramCount > count($pageArguments)) {
                $required = $pageCallback->getNumberOfRequiredParameters() - count($pageArguments);
                if ($required > 0) {
                    for ($i = 0; $i < $required; ++$i) {
                        $placeholder = 'arg' . ++$key;
                        $parameters[$placeholder] = [
                            'converter' => PageArgumentsConverter::class,
                        ];
                        $pathParts[] = '{' . $placeholder . '}';
                    }
                }
                $optional = $paramCount - $required;
                if ($optional > 0) {
                    for ($i = 0; $i < $optional; ++$i) {
                        $placeholder = 'arg' . ++$key;
                        $parameters[$placeholder] = [
                            'converter' => PageArgumentsConverter::class,
                        ];
                        $defaults[$placeholder] = null;
                        $pathParts[] = '{' . $placeholder . '}';
                    }
                }
            }
        }
        $route = new Route('/' . implode('/', $pathParts));
        $route->addDefaults($defaults);
        $route->setDefault('_title', $definition['title'] ?? '');

        $titleCallback = $definition['title callback'] ?? '';
        if ($titleCallback !== '') {
            $route->setDefault('_title_callback', '\Retrofit\Drupal\Controller\PageCallbackController::getTitle');
            $titleArguments = $definition['title arguments'] ?? [];
            foreach ($titleArguments as &$titleArgument) {
                if (is_int($titleArgument)) {
                    $titleArgument = $pathParts[$titleArgument];
                }
            }
            $route->setDefault('_custom_title_callback', $titleCallback);
            $route->setDefault('_custom_title_arguments', $titleArguments);
        }

        if ($definition['page callback'] === 'drupal_get_form') {
            $route->setDefault('_controller', '\Retrofit\Drupal\Controller\DrupalGetFormController::getForm');
            $route->setDefault('_form_id', array_shift($pageArguments));
        } else {
            $route->setDefault('_controller', '\Retrofit\Drupal\Controller\PageCallbackController::getPage');
            $route->setDefault('_menu_callback', $definition['page callback']);
        }

        $accessCallback = $definition['access callback'] ?? '';
        $accessArguments = $definition['access arguments'] ?? [];
        if ($accessCallback === '' || $accessCallback === 'user_access') {
            $route->setRequirement('_permission', reset($accessArguments) ?: '');
        } elseif (is_bool($accessCallback)) {
            $route->setRequirement('_access', $accessCallback ? 'TRUE' : 'FALSE');
        } else {
            $route->setRequirement('_custom_access', '\Retrofit\Drupal\Access\CustomControllerAccessCallback::check');
            $route->setDefault('_custom_access_callback', $accessCallback);
            foreach ($accessArguments as &$accessArgument) {
                if (is_int($accessArgument)) {
                    $accessArgument = $pathParts[$accessArgument];
                }
            }
            $route->setDefault('_custom_access_arguments', $accessArguments);
        }

        $route->setOption('module', $module);
        if (isset($definition['file'])) {
            $route->setOption('file path', $definition['file path']);
            $route->setOption('file', $definition['file']);
        }
        $route->setDefault('_custom_page_arguments', $pageArguments);
        if (count($parameters) > 0) {
            $route->setOption('parameters', $parameters);
        }
        return $route;
    }
}
