<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindSeoHead;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoSettings;
use AlexandreBulete\DddSeoBundle\Domain\Model\SeoTemplate;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoFields;

/**
 * Everything that decides a page's SEO, before the application's variables
 * fill the patterns: what was set by hand, the kind's template, the site's
 * rules.
 */
final readonly class SeoHead
{
    public function __construct(
        public SeoFields $fields,
        public SeoTemplate $template,
        public SeoSettings $settings,
    ) {}
}
