<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * /sitemap.xml and /robots.txt. Sylius routes the back office itself.
 */
return static function (RoutingConfigurator $routes): void {
    $routes->import(dirname(__DIR__) . '/src/Infrastructure/Symfony/Controller/', 'attribute');
};
