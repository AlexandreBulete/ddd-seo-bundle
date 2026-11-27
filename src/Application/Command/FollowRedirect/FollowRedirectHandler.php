<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Command\FollowRedirect;

use AlexandreBulete\DddSeoBundle\Domain\Repository\RedirectRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectSource;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class FollowRedirectHandler
{
    public function __construct(
        private RedirectRepositoryInterface $redirects,
        private ClockInterface $clock,
    ) {}

    public function __invoke(FollowRedirectCommand $command): ?string
    {
        try {
            $source = new RedirectSource($command->path);
        } catch (\InvalidArgumentException) {
            return null;
        }
        $redirect = $this->redirects->findBySource($source->path);
        if ($redirect === null) {
            return null;
        }
        $redirect->follow($this->clock->now());
        $this->redirects->save($redirect);

        return $redirect->target;
    }
}
