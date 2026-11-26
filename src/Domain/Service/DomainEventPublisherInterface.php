<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Service;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

interface DomainEventPublisherInterface
{
    /**
     * @param iterable<DomainEvent> $events
     */
    public function publishAll(iterable $events): void;
}
