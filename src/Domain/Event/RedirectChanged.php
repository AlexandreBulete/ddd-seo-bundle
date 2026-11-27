<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * A redirect leads elsewhere, or starts from another address.
 */
final readonly class RedirectChanged implements DomainEvent
{
    public function __construct(
        public string $redirectId,
        public string $source,
    ) {}
}
