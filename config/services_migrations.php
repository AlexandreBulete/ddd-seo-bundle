<?php

declare(strict_types=1);

use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\Migrations\Version20261127140000;
use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\Migrations\Version20261128100000;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * Loaded only when DoctrineMigrationsBundle is enabled. Each migration is a
 * service named after its class, which is also its recorded version.
 *
 * Listed one by one, never scanned: a stray class must not turn into a
 * schema change.
 */
return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->tag('doctrine_migrations.migration');

    foreach ([Version20261127140000::class, Version20261128100000::class] as $migration) {
        $services->set($migration);
    }
};
