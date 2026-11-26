<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * The hand-set SEO of a page changed; `target` is "kind:reference".
 */
final readonly class SeoEntryChanged implements DomainEvent
{
    public function __construct(
        public string $target,
    ) {}
}
