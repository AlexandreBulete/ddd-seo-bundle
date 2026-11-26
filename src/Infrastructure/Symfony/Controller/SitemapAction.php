<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Controller;

use AlexandreBulete\DddSeoBundle\Application\Query\FindSitemapExclusions\FindSitemapExclusionsQuery;
use AlexandreBulete\DddSeoBundle\Application\Query\FindSitemapExclusions\SitemapExclusions;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sitemap\SitemapSourceInterface;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Every public page the sources declare, minus those set to noindex or
 * pointing elsewhere (a canonical set by hand). Empty while the site may not
 * be indexed.
 */
final readonly class SitemapAction
{
    /**
     * @param iterable<SitemapSourceInterface> $sources
     */
    public function __construct(
        private QueryBusInterface $queryBus,
        #[AutowireIterator(SitemapSourceInterface::TAG)]
        private iterable $sources,
    ) {}

    #[Route(path: '/sitemap.xml', name: 'seo_sitemap', methods: ['GET'], format: 'xml')]
    public function __invoke(Request $request): Response
    {
        /** @var SitemapExclusions $exclusions */
        $exclusions = $this->queryBus->ask(new FindSitemapExclusionsQuery());
        $host = $request->getSchemeAndHttpHost() . $request->getBaseUrl();

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $seen = [];
        if ($exclusions->indexing) {
            foreach ($this->sources as $source) {
                foreach ($source->entries() as $entry) {
                    $url = str_starts_with($entry->url, '/') ? $host . $entry->url : $entry->url;
                    if ($exclusions->excludes($source->kind(), $entry->reference) || isset($seen[$url])) {
                        continue;
                    }
                    $seen[$url] = true;
                    $xml->startElement('url');
                    $xml->writeElement('loc', $url);
                    if ($entry->lastModified !== null) {
                        $xml->writeElement('lastmod', $entry->lastModified->format('Y-m-d'));
                    }
                    $xml->endElement();
                }
            }
        }
        $xml->endElement();
        $xml->endDocument();

        return new Response($xml->outputMemory(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
