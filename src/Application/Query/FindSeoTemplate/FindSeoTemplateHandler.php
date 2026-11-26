<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindSeoTemplate;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoTemplate;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoTemplateRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

#[AsQueryHandler]
final readonly class FindSeoTemplateHandler
{
    public function __construct(
        private SeoTemplateRepositoryInterface $templates,
    ) {}

    public function __invoke(FindSeoTemplateQuery $query): SeoTemplate
    {
        return $this->templates->get($query->kind);
    }
}
