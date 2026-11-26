<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindSeoSettings;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoSettings;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoSettingsRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

#[AsQueryHandler]
final readonly class FindSeoSettingsHandler
{
    public function __construct(
        private SeoSettingsRepositoryInterface $settings,
    ) {}

    public function __invoke(FindSeoSettingsQuery $query): SeoSettings
    {
        return $this->settings->get();
    }
}
