<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Grid\SeoPageGrid;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Processor\UpdateSeoPageProcessor;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Provider\SeoPageItemProvider;
use AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form\Admin\SeoPageType;
use AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form\SeoFieldsData;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A fixed page of the site (`seo.routes`): its id is the route name.
 */
#[AsResource(
    alias: 'seo.seo_page',
    section: 'admin',
    formType: SeoPageType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    name: 'seo_page',
    pluralName: 'seo_pages',
    driver: false,
    operations: [
        new Update(provider: SeoPageItemProvider::class, processor: UpdateSeoPageProcessor::class, notificationMessage: 'seo.flash.page_saved'),
        new Index(grid: SeoPageGrid::class),
    ],
)]
final class SeoPageResource implements ResourceInterface
{
    public function __construct(
        public string $id = '',
        public string $label = '',
        public ?string $path = null,
        #[Assert\Valid]
        public SeoFieldsData $seo = new SeoFieldsData(),
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function titleOrDash(): string
    {
        return $this->seo->title ?? '—';
    }
}
