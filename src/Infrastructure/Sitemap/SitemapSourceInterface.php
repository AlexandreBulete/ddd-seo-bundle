<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sitemap;

/**
 * The public pages of one kind, declared by the application for the
 * sitemap. Autoconfigured: an application service implementing it is tagged
 * {@see self::TAG}. Only what a visitor can see belongs here.
 */
interface SitemapSourceInterface
{
    public const string TAG = 'ddd_seo.sitemap_source';

    public function kind(): string;

    /**
     * @return iterable<SitemapEntry>
     */
    public function entries(): iterable;
}
