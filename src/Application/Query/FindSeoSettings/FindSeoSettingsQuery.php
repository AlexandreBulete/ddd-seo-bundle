<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindSeoSettings;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoSettings;
use AlexandreBulete\DddFoundation\Application\Authorization\Permission;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * The site-wide SEO rules.
 *
 * @implements QueryInterface<SeoSettings>
 */
#[Permission('seo.configure')]
final readonly class FindSeoSettingsQuery implements QueryInterface
{
}
