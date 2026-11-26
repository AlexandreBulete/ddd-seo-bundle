<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Event;

use AlexandreBulete\DddFoundation\Domain\Event\DomainEvent;

/**
 * The site-wide SEO settings changed.
 */
final readonly class SeoSettingsChanged implements DomainEvent
{
}
