<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Command\AddRedirect;

use AlexandreBulete\DddSeoBundle\Domain\Model\Redirect;
use AlexandreBulete\DddSeoBundle\Domain\Repository\RedirectRepositoryInterface;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\RedirectId;
use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class AddRedirectHandler
{
    public function __construct(
        private RedirectRepositoryInterface $redirects,
        private ClockInterface $clock,
    ) {}

    public function __invoke(AddRedirectCommand $command): Redirect
    {
        $now = $this->clock->now();
        $source = $command->source;
        $target = $command->target;

        // The new address answers again: it is no longer an old one.
        $targetPath = $target->path();
        $back = $targetPath !== null ? $this->redirects->findBySource($targetPath) : null;
        if ($back !== null) {
            $back->remove();
            $this->redirects->remove($back);
        }

        // No chains: whatever led to the old address leads to the new one.
        foreach ($this->redirects->leadingTo($source->path) as $earlier) {
            if ($earlier->source === $targetPath) {
                continue;
            }
            $earlier->shortcut($target, $now);
            $this->redirects->save($earlier);
        }

        $redirect = $this->redirects->findBySource($source->path);
        if ($redirect === null) {
            $redirect = Redirect::add(RedirectId::generate(), $source, $target, $command->automatic, $now);
        } else {
            $redirect->change($source, $target, $now);
        }
        $this->redirects->save($redirect);

        return $redirect;
    }
}
