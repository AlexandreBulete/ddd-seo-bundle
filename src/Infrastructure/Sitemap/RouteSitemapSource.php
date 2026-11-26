<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sitemap;

use AlexandreBulete\DddSeoBundle\Infrastructure\Config\SeoKinds;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The application's fixed pages (`seo.routes`), each at its route; first in
 * the sitemap.
 */
#[AsTaggedItem(priority: 100)]
final readonly class RouteSitemapSource implements SitemapSourceInterface
{
    public function __construct(
        private SeoKinds $kinds,
        private UrlGeneratorInterface $urls,
    ) {}

    public function kind(): string
    {
        return SeoKinds::ROUTE;
    }

    public function entries(): iterable
    {
        foreach (array_keys($this->kinds->routes()) as $route) {
            try {
                yield new SitemapEntry($route, $this->urls->generate($route));
            } catch (RoutingException) {
                // A route that needs parameters, or is gone: not a fixed page.
            }
        }
    }
}
