<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * A redirect was added, by hand or because an address changed.
 */
final readonly class RedirectAdded implements DomainEvent
{
    public function __construct(
        public string $redirectId,
        public string $source,
    ) {}
}
