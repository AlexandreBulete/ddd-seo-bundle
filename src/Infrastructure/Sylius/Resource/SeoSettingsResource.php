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
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
        #[Assert\Length(max: SeoSettings::PERSON_NAME_MAX_LENGTH)]
        public ?string $personName = null,
        #[Assert\Length(max: SeoSettings::URL_MAX_LENGTH)]
        public ?string $personUrl = null,
        // One address per line.
        #[Assert\Length(max: 5000)]
        public ?string $personSameAs = null,
    ) {}

    /**
     * The domain's rule, said on the fields: a page about the person and
     * profiles that are addresses.
     */
    #[Assert\Callback]
    public function validatePerson(ExecutionContextInterface $context): void
    {
        try {
            SeoSettings::defaults()->describePerson($this->personName, $this->personUrl, [], new \DateTimeImmutable());
        } catch (\InvalidArgumentException) {
            $context->buildViolation('seo.person.invalid_url')->atPath('personUrl')->addViolation();
        }
        foreach ($this->sameAsList() as $link) {
            try {
                SeoSettings::defaults()->describePerson(null, null, [$link], new \DateTimeImmutable());
            } catch (\InvalidArgumentException) {
                $context->buildViolation('seo.person.invalid_profile')->setParameter('%link%', $link)->atPath('personSameAs')->addViolation();
            }
        }
    }

    /**
     * @return list<string>
     */
    public function sameAsList(): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", (string) $this->personSameAs)), static fn (string $line): bool => $line !== ''));
    }

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
            $settings->personName,
            $settings->personUrl,
            implode("\n", $settings->personSameAs ?? []),
        );
    }
}
