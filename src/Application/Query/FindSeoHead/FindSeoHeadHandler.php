<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindSeoHead;

use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoEntryRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoSettingsRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoTemplateRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoFields;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

#[AsQueryHandler]
final readonly class FindSeoHeadHandler
{
    public function __construct(
        private SeoEntryRepositoryInterface $entries,
        private SeoTemplateRepositoryInterface $templates,
        private SeoSettingsRepositoryInterface $settings,
    ) {}

    public function __invoke(FindSeoHeadQuery $query): SeoHead
    {
        return new SeoHead(
            $this->entries->findByTarget($query->target)?->fields() ?? new SeoFields(),
            $this->templates->get($query->target->kind),
            $this->settings->get(),
        );
    }
}
