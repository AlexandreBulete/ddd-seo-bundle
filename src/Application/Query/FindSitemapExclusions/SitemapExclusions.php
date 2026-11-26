<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Application\Query\FindSitemapExclusions;

final readonly class SitemapExclusions
{
    /**
     * @param array<string, true> $targets "kind:reference" => true
     */
    public function __construct(
        public bool $indexing,
        public array $targets,
    ) {}

    public function excludes(string $kind, string $reference): bool
    {
        return isset($this->targets[$kind . ':' . $reference]);
    }
}
