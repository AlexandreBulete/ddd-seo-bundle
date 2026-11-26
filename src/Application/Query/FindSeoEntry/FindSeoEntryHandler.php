<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindSeoEntry;

use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoEntryRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoFields;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

#[AsQueryHandler]
final readonly class FindSeoEntryHandler
{
    public function __construct(
        private SeoEntryRepositoryInterface $entries,
    ) {}

    public function __invoke(FindSeoEntryQuery $query): SeoFields
    {
        return $this->entries->findByTarget($query->target)?->fields() ?? new SeoFields();
    }
}
