<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Command\RemoveRedirect;

use AlexandreBulete\DddSeoBundle\Domain\Exception\RedirectNotFoundException;
use AlexandreBulete\DddSeoBundle\Domain\Repository\RedirectRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;

#[AsCommandHandler]
final readonly class RemoveRedirectHandler
{
    public function __construct(
        private RedirectRepositoryInterface $redirects,
    ) {}

    public function __invoke(RemoveRedirectCommand $command): void
    {
        $redirect = $this->redirects->find($command->id) ?? throw new RedirectNotFoundException((string) $command->id);
        $redirect->remove();
        $this->redirects->remove($redirect);
    }
}
