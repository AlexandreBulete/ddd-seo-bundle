<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sitemap;

/**
 * One public page for the sitemap: its reference (to honour what was set by
 * hand on it), its address (absolute, or a path of this site), when it last
 * changed.
 */
final readonly class SitemapEntry
{
    public function __construct(
        public string $reference,
        public string $url,
        public ?\DateTimeImmutable $lastModified = null,
    ) {}
}
