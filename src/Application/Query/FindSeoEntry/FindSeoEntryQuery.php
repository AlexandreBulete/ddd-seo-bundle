<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindSeoEntry;

use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoFields;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoTarget;
use AlexandreBulete\DddFoundation\Application\Authorization\Permission;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * What was set by hand for a page; all empty when nothing was.
 *
 * @implements QueryInterface<SeoFields>
 */
#[Permission('seo.edit')]
final readonly class FindSeoEntryQuery implements QueryInterface
{
    public function __construct(
        public SeoTarget $target,
    ) {}
}
