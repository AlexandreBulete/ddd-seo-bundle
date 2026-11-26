<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindSeoTemplate;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoTemplate;
use AlexandreBulete\DddFoundation\Application\Authorization\Permission;
use AlexandreBulete\DddFoundation\Application\Query\QueryInterface;

/**
 * The template of a kind, saved or empty.
 *
 * @implements QueryInterface<SeoTemplate>
 */
#[Permission('seo.configure')]
final readonly class FindSeoTemplateQuery implements QueryInterface
{
    public function __construct(
        public string $kind,
    ) {}
}
