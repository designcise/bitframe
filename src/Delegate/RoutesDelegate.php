<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2018 Daniyal Hamid (https://designcise.com)
 *
 * @license   https://github.com/designcise/bitframe/blob/master/LICENSE.md MIT License
 */

namespace BitFrame\Delegate;

use BitFrame\Router\RouteCollectionInterface;

/**
 * Helps define route definitions in one place and easily
 * inject them into a route collection.
 */
class RoutesDelegate {
    /**
     * Inject routes from a configuration array.
     *
     * The following configuration structure can be used to define routes:
     *
     * [
     *     [
     *         'method' => ['GET', 'POST', 'PATCH'],
     *         'path' => '/path/to/match',
     *         'controller' => 'A callable (e.g. class or function)'
     *     ],
     *     // etc.
     * ];
     *
     * @param array $routes An array of routes that can have 'method', 'path' and 'controller' keys.
     *
     * Note:
     *     - Routes without a 'controller' defined are skipped/ignored.
     *     - When 'method' aren't defined, a default 'GET' method is used.
     *     - When 'path' isn't defined, a default path '/' is used.
     */
    public static function fromConfig(RouteCollectionInterface $routeCollector, array $routes)
    {
        foreach($routes as $route) {
            // no controller specified?
            if (! isset($route['controller'])) {
                // skip, because no controller means nothing to do
                continue;
            }
            
            // set value, or defaults
            $method = $route['method'] ?? 'GET';
            settype($method, 'array');
            $method = array_map('strtoupper', $method);
            
            $path = '/' . ltrim($route['path'] ?? '', '/');
            
            $routeCollector->map($method, $path, $route['controller']);
        }
    }
}