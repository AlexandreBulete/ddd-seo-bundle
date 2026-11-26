<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoSettings;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Grid\SeoSettingsGrid;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Processor\UpdateSeoSettingsProcessor;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\State\Provider\SeoSettingsItemProvider;
use AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form\Admin\SeoSettingsType;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The site-wide rules: one row.
 */
#[AsResource(
    alias: 'seo.seo_settings',
    section: 'admin',
    formType: SeoSettingsType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    name: 'seo_settings',
    pluralName: 'seo_settings',
    driver: false,
    operations: [
        new Update(provider: SeoSettingsItemProvider::class, processor: UpdateSeoSettingsProcessor::class, notificationMessage: 'seo.flash.settings_saved'),
        new Index(grid: SeoSettingsGrid::class),
    ],
)]
final class SeoSettingsResource implements ResourceInterface
{
    public function __construct(
        public string $id = SeoSettings::KEY,
        public bool $indexing = true,
        #[Assert\Length(max: SeoSettings::ROBOTS_MAX_LENGTH)]
        #[Assert\Regex(pattern: '/^\s*sitemap\s*:/im', match: false, message: 'seo.robots.no_sitemap')]
        public ?string $robotsRules = null,
        #[Assert\Length(max: SeoSettings::TITLE_MAX_LENGTH)]
        public ?string $titlePattern = null,
        public ?\DateTimeImmutable $updatedAt = null,
        public ?string $indexingLabel = null,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public static function fromModel(SeoSettings $settings): self
    {
        return new self(
            $settings->key,
            $settings->indexing,
            $settings->robotsRules,
            $settings->titlePattern,
            $settings->updatedAt,
            $settings->indexing ? 'seo.settings.indexing_on' : 'seo.settings.indexing_off',
        );
    }
}
