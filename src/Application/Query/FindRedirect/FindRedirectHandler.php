<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindRedirect;

use AlexandreBulete\DddSeoBundle\Domain\Exception\RedirectNotFoundException;
use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddSeoBundle\Domain\Repository\RedirectRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

#[AsQueryHandler]
final readonly class FindRedirectHandler
{
    public function __construct(
        private RedirectRepositoryInterface $redirects,
    ) {}

    public function __invoke(FindRedirectQuery $query): Redirect
    {
        return $this->redirects->find($query->id) ?? throw new RedirectNotFoundException((string) $query->id);
    }
}
