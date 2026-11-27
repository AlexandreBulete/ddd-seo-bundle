<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoTemplate;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Grid\SeoTemplateGrid;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Processor\UpdateSeoTemplateProcessor;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Provider\SeoTemplateItemProvider;
use AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form\Admin\SeoTemplateType;
use AlexandreBulete\DddSyliusBundle\Admin\View\AdminRecordInterface;
use AlexandreBulete\DddSyliusBundle\Admin\View\StatusBadge;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * How the pages of one kind are titled and described: its id is the kind.
 */
#[AsResource(
    alias: 'seo.seo_template',
    section: 'admin',
    formType: SeoTemplateType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    name: 'seo_template',
    pluralName: 'seo_templates',
    driver: false,
    operations: [
        new Update(provider: SeoTemplateItemProvider::class, processor: UpdateSeoTemplateProcessor::class, notificationMessage: 'seo.flash.template_saved'),
        new Index(grid: SeoTemplateGrid::class),
    ],
)]
final class SeoTemplateResource implements ResourceInterface, AdminRecordInterface
{
    /**
     * @param array<string, string> $variables name => what it holds
     */
    public function __construct(
        public string $id = '',
        public string $label = '',
        #[Assert\Length(max: SeoTemplate::PATTERN_MAX_LENGTH)]
        public ?string $title = null,
        #[Assert\Length(max: SeoTemplate::PATTERN_MAX_LENGTH)]
        public ?string $description = null,
        public ?string $defaultTitle = null,
        public ?string $defaultDescription = null,
        public array $variables = [],
        public ?\DateTimeImmutable $updatedAt = null,
        // For the list: what applies when neither is set.
        public string $titleFallback = '—',
        public string $descriptionFallback = '—',
        public bool $structuredData = true,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function effectiveTitle(): string
    {
        return $this->title ?? $this->defaultTitle ?? $this->titleFallback;
    }

    public function effectiveDescription(): string
    {
        return $this->description ?? $this->defaultDescription ?? $this->descriptionFallback;
    }

    public function adminLabel(): ?string
    {
        return $this->label === '' ? null : $this->label;
    }

    public function adminStatus(): ?StatusBadge
    {
        return null;
    }

    public function adminPublicUrl(): ?string
    {
        return null;
    }
}
