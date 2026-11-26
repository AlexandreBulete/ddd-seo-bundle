<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindSeoEntries;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoEntry;
use AlexandreBulete\DddFoundation\Application\Authorization\Permission;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * The pages of a kind that have SEO set by hand.
 *
 * @implements QueryInterface<list<SeoEntry>>
 */
#[Permission('seo.edit')]
final readonly class FindSeoEntriesQuery implements QueryInterface
{
    public function __construct(
        public string $kind,
    ) {}
}
