<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Command\ChangeRedirect;

use AlexandreBulete\DddSeoBundle\Domain\Exception\RedirectNotFoundException;
use AlexandreBulete\DddSeoBundle\Domain\Exception\RedirectSourceTakenException;
use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddSeoBundle\Domain\Repository\RedirectRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class ChangeRedirectHandler
{
    public function __construct(
        private RedirectRepositoryInterface $redirects,
        private ClockInterface $clock,
    ) {}

    public function __invoke(ChangeRedirectCommand $command): Redirect
    {
        $redirect = $this->redirects->find($command->id) ?? throw new RedirectNotFoundException((string) $command->id);
        $other = $this->redirects->findBySource($command->source->path);
        if ($other !== null && !$other->id->equals($redirect->id)) {
            throw new RedirectSourceTakenException($command->source->path);
        }
        $redirect->change($command->source, $command->target, $this->clock->now());
        $this->redirects->save($redirect);

        return $redirect;
    }
}
