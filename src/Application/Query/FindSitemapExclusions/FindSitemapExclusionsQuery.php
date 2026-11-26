<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindSitemapExclusions;

use AlexandreBulete\DddFoundation\Application\Authorization\PublicAccess;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * The pages kept out of the sitemap ("kind:reference"), and whether the site
 * may be indexed at all.
 *
 * @implements QueryInterface<SitemapExclusions>
 */
#[PublicAccess]
final readonly class FindSitemapExclusionsQuery implements QueryInterface
{
}
