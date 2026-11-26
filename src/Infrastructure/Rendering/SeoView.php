<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Rendering;

/**
 * A page's head, resolved: what the templates print.
 */
final readonly class SeoView
{
    public function __construct(
        public string $title,
        public ?string $description,
        public ?string $canonical,
        public bool $noindex,
        public string $shareTitle,
        public ?string $shareDescription,
        public ?string $image,
        public string $type,
        public ?string $siteName,
        public ?string $locale,
    ) {}

    public function robots(): ?string
    {
        return $this->noindex ? 'noindex, follow' : null;
    }
}
