<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddSeoBundle\Domain\Service\DomainEventPublisherInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Default adapter for {@see DomainEventPublisherInterface}: hands the events
 * to the application dispatcher, inside the repository's transaction. Bind an
 * outbox-backed adapter onto the interface for delivery outside the process.
 */
final readonly class ImmediateEventPublisher implements DomainEventPublisherInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function publishAll(iterable $events): void
    {
        foreach ($events as $event) {
            $this->dispatcher->dispatch($event);
        }
    }
}
