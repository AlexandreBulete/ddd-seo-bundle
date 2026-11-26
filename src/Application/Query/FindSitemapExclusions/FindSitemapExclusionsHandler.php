<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindSitemapExclusions;

use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoEntryRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoSettingsRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

#[AsQueryHandler]
final readonly class FindSitemapExclusionsHandler
{
    public function __construct(
        private SeoEntryRepositoryInterface $entries,
        private SeoSettingsRepositoryInterface $settings,
    ) {}

    public function __invoke(FindSitemapExclusionsQuery $query): SitemapExclusions
    {
        $targets = [];
        foreach ($this->entries->excludedFromSitemap() as $entry) {
            $targets[(string) $entry->target] = true;
        }

        return new SitemapExclusions($this->settings->get()->indexing, $targets);
    }
}
