<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindRedirects;

use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddSeoBundle\Domain\Repository\RedirectRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

#[AsQueryHandler]
final readonly class FindRedirectsHandler
{
    public function __construct(
        private RedirectRepositoryInterface $redirects,
    ) {}

    /**
     * @return list<Redirect>
     */
    public function __invoke(FindRedirectsQuery $query): array
    {
        return $this->redirects->all($query->search);
    }
}
