<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Controller;

use AlexandreBulete\DddSeoBundle\Application\Query\FindSitemapExclusions\FindSitemapExclusionsQuery;
use AlexandreBulete\DddSeoBundle\Application\Query\FindSitemapExclusions\SitemapExclusions;
use AlexandreBulete\DddSeoBundle\Domain\Repository\SeoSettingsRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;

/**
 * The rules set in the back office, then the sitemap. While the site may not
 * be indexed: everything is disallowed, whatever the rules say.
 */
final readonly class RobotsAction
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private SeoSettingsRepositoryInterface $settings,
        private UrlGeneratorInterface $urls,
    ) {}

    #[Route(path: '/robots.txt', name: 'seo_robots', methods: ['GET'], format: 'txt')]
    public function __invoke(Request $request): Response
    {
        /** @var SitemapExclusions $exclusions */
        $exclusions = $this->queryBus->ask(new FindSitemapExclusionsQuery());
        $body = $exclusions->indexing
            ? $this->settings->get()->robotsRules . "\n\nSitemap: " . $this->urls->generate('seo_sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL) . "\n"
            : "User-agent: *\nDisallow: /\n";

        return new Response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
