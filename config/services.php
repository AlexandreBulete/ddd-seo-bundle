<?php

declare(strict_types=1);

use AlexandreBulete\DddSeoBundle\Domain\Repository\RedirectRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoEntryRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoSettingsRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoTemplateRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\Service\DomainEventPublisherInterface;
use AlexandreBulete\DddSeoBundle\Domain\Service\SeoKindCatalogInterface;
use AlexandreBulete\DddSeoBundle\Infrastructure\Config\SeoKinds;
use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\DoctrineRedirectRepository;
use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\DoctrineSeoEntryRepository;
use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\DoctrineSeoSettingsRepository;
use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\DoctrineSeoTemplateRepository;
use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\ImmediateEventPublisher;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $src = dirname(__DIR__) . '/src';
    $services = $container->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('AlexandreBulete\DddSeoBundle\\', $src . '/')
        ->exclude([
            // Aggregates and value objects are never services.
            $src . '/Domain',
            $src . '/DddSeoBundle.php',
            // Doctrine instantiates types itself.
            $src . '/Infrastructure/Doctrine/Type',
            $src . '/Infrastructure/Doctrine/Mapping',
            // Conditional: services_migrations.php, services_admin.php.
            $src . '/Infrastructure/Doctrine/Migrations',
            $src . '/Infrastructure/Sylius',
            $src . '/Infrastructure/Symfony/Form',
            $src . '/Infrastructure/Symfony/Validator',
            // Data, not services.
            $src . '/Application/Query/FindSeoHead/SeoHead.php',
            $src . '/Application/Query/FindSitemapExclusions/SitemapExclusions.php',
            $src . '/Infrastructure/Rendering/Pattern.php',
            $src . '/Infrastructure/Rendering/SeoView.php',
            $src . '/Infrastructure/Sitemap/SitemapEntry.php',
            $src . '/Infrastructure/Sitemap/SitemapSourceInterface.php',
        ]);

    $services->load('AlexandreBulete\DddSeoBundle\\Infrastructure\\Symfony\\Controller\\', $src . '/Infrastructure/Symfony/Controller/')
        ->tag('controller.service_arguments');

    // ── Ports → adapters ────────────────────────────────────────────────────
    $services->alias(SeoEntryRepositoryInterface::class, DoctrineSeoEntryRepository::class);
    $services->alias(SeoTemplateRepositoryInterface::class, DoctrineSeoTemplateRepository::class);
    $services->alias(SeoSettingsRepositoryInterface::class, DoctrineSeoSettingsRepository::class);
    $services->alias(RedirectRepositoryInterface::class, DoctrineRedirectRepository::class);
    $services->alias(SeoKindCatalogInterface::class, SeoKinds::class);
    $services->alias(DomainEventPublisherInterface::class, ImmediateEventPublisher::class);
};
