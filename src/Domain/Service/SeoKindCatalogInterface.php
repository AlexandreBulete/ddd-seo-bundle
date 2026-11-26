<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Domain\Service;

/**
 * The kinds of pages the application declares.
 */
interface SeoKindCatalogInterface
{
    public function has(string $kind): bool;
}
