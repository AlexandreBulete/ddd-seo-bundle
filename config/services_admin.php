<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * Loaded only when `seo.admin.enabled` is true: everything Sylius and the
 * back-office forms, the SEO block of content forms included. Resources are
 * DTOs, not services.
 */
return static function (ContainerConfigurator $container): void {
    $src = dirname(__DIR__) . '/src';
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('AlexandreBulete\DddSeoBundle\\Infrastructure\\Sylius\\', $src . '/Infrastructure/Sylius/')
        ->exclude([
            $src . '/Infrastructure/Sylius/Resource/SeoPageResource.php',
            $src . '/Infrastructure/Sylius/Resource/SeoTemplateResource.php',
            $src . '/Infrastructure/Sylius/Resource/SeoSettingsResource.php',
        ]);
    $services->load('AlexandreBulete\DddSeoBundle\\Infrastructure\\Symfony\\Form\\', $src . '/Infrastructure/Symfony/Form/')
        ->exclude([$src . '/Infrastructure/Symfony/Form/SeoFieldsData.php']);
};
