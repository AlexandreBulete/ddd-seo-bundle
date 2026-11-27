<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * A redirect was removed.
 */
final readonly class RedirectRemoved implements DomainEvent
{
    public function __construct(
        public string $redirectId,
        public string $source,
    ) {}
}
