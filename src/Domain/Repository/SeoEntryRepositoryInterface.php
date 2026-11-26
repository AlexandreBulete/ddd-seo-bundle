<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Repository;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoEntry;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoTarget;

interface SeoEntryRepositoryInterface
{
    public function findByTarget(SeoTarget $target): ?SeoEntry;

    /**
     * @return list<SeoEntry>
     */
    public function ofKind(string $kind): array;

    /**
     * The entries keeping their page out of the sitemap: noindex, or a
     * canonical address set by hand.
     *
     * @return list<SeoEntry>
     */
    public function excludedFromSitemap(): array;

    public function save(SeoEntry $entry): void;

    public function remove(SeoEntry $entry): void;
}
