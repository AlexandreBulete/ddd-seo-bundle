<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindSeoEntries;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoEntry;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoEntryRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

#[AsQueryHandler]
final readonly class FindSeoEntriesHandler
{
    public function __construct(
        private SeoEntryRepositoryInterface $entries,
    ) {}

    /**
     * @return list<SeoEntry>
     */
    public function __invoke(FindSeoEntriesQuery $query): array
    {
        return $this->entries->ofKind($query->kind);
    }
}
