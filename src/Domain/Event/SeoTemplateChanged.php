<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * The template of a kind of content changed.
 */
final readonly class SeoTemplateChanged implements DomainEvent
{
    public function __construct(
        public string $kind,
    ) {}
}
