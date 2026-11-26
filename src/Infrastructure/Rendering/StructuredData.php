<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Rendering;

use AlexandreBulete\DddSeoBundle\Application\Query\FindSeoHead\SeoHead;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * A page's JSON-LD, one `@graph`:
 *
 * - the site (`WebSite`), with its search when `seo.search` names a route;
 * - the person behind it (`Person`), when the settings name one;
 * - the page itself: the `schema` variable, a schema.org node the
 *   application builds (`Review`, `BlogPosting`, `CollectionPage`…), given
 *   its address, language, author and site;
 * - its trail (`BreadcrumbList`), from the `breadcrumbs` variable;
 * - what was set by hand on the page, as written.
 *
 * The kind's switch turns off the page and its trail, never the site nor
 * what was set by hand.
 */
final readonly class StructuredData
{
    /** Types written by someone: given the site's person as author. */
    private const array AUTHORED = ['Article', 'BlogPosting', 'NewsArticle', 'Review', 'CreativeWork'];
    private const string SEARCH_TERM = 'SEARCH_TERM_PLACEHOLDER';

    /**
     * @param array{route: ?string, parameter: string} $search
     */
    public function __construct(
        private UrlGeneratorInterface $urls,
        #[Autowire(param: 'seo.search')]
        private array $search,
    ) {}

    /**
     * @param array<string, mixed> $variables
     */
    public function encode(SeoHead $head, array $variables, ?Request $request, ?string $canonical, ?string $site, ?string $locale): ?string
    {
        $home = $request !== null ? $request->getSchemeAndHttpHost() . $request->getBaseUrl() . '/' : null;
        $language = $locale !== null ? str_replace('_', '-', $locale) : null;
        $settings = $head->settings;
        $graph = [];

        $person = null;
        if ($settings->personName !== null && $home !== null) {
            $person = ['@id' => $home . '#person'];
            $graph[] = self::clean([
                '@type' => 'Person',
                '@id' => $person['@id'],
                'name' => $settings->personName,
                'url' => self::absolute($settings->personUrl, $request),
                'sameAs' => $settings->personSameAs,
            ]);
        }

        $website = null;
        if ($site !== null && $home !== null) {
            $website = ['@id' => $home . '#website'];
            $graph[] = self::clean([
                '@type' => 'WebSite',
                '@id' => $website['@id'],
                'url' => $home,
                'name' => $site,
                'inLanguage' => $language,
                'publisher' => $person,
                'potentialAction' => $this->searchAction(),
            ]);
        }

        if ($head->template->structuredData) {
            $schema = $variables['schema'] ?? null;
            $type = is_array($schema) ? ($schema['@type'] ?? null) : null;
            if (is_array($schema) && is_string($type)) {
                /** @var array<string, mixed> $schema */
                $node = $schema + ['@id' => $canonical !== null ? $canonical . '#main' : null, 'url' => $canonical, 'inLanguage' => $language];
                if (in_array($type, self::AUTHORED, true)) {
                    $node += ['author' => $person, 'publisher' => $person];
                }
                if (str_ends_with($type, 'Page')) {
                    $node += ['isPartOf' => $website];
                    if ($type === 'AboutPage') {
                        $node += ['mainEntity' => $person];
                    }
                }
                $graph[] = self::clean($node);
            }

            $trail = self::breadcrumbs($variables['breadcrumbs'] ?? null, $request, $canonical);
            if ($trail !== null) {
                $graph[] = $trail;
            }
        }

        if ($head->fields->jsonLd !== null) {
            /** @var array<mixed> $manual */
            $manual = json_decode($head->fields->jsonLd, true, 32, \JSON_THROW_ON_ERROR);
            foreach (array_is_list($manual) ? $manual : [$manual] as $node) {
                if (is_array($node)) {
                    unset($node['@context']);
                    $graph[] = $node;
                }
            }
        }

        if ($graph === []) {
            return null;
        }

        return json_encode(['@context' => 'https://schema.org', '@graph' => $graph], \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_HEX_TAG | \JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function searchAction(): ?array
    {
        if ($this->search['route'] === null) {
            return null;
        }
        try {
            $url = $this->urls->generate($this->search['route'], [$this->search['parameter'] => self::SEARCH_TERM], UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (RoutingException) {
            return null;
        }

        return [
            '@type' => 'SearchAction',
            'target' => ['@type' => 'EntryPoint', 'urlTemplate' => str_replace(self::SEARCH_TERM, '{search_term_string}', $url)],
            'query-input' => 'required name=search_term_string',
        ];
    }

    /**
     * From [{name, url}, …]; the last one is the page itself.
     *
     * @return array<string, mixed>|null
     */
    private static function breadcrumbs(mixed $crumbs, ?Request $request, ?string $canonical): ?array
    {
        if (!is_array($crumbs) || $crumbs === []) {
            return null;
        }
        $items = [];
        $last = count($crumbs) - 1;
        foreach (array_values($crumbs) as $position => $crumb) {
            if (!is_array($crumb) || !is_string($crumb['name'] ?? null) || $crumb['name'] === '') {
                return null;
            }
            $url = is_string($crumb['url'] ?? null) ? self::absolute($crumb['url'], $request) : null;
            $items[] = self::clean([
                '@type' => 'ListItem',
                'position' => $position + 1,
                'name' => $crumb['name'],
                'item' => $url ?? ($position === $last ? $canonical : null),
            ]);
        }

        return ['@type' => 'BreadcrumbList', 'itemListElement' => $items];
    }

    private static function absolute(?string $url, ?Request $request): ?string
    {
        if ($url === null || $request === null || !str_starts_with($url, '/') || str_starts_with($url, '//')) {
            return $url;
        }

        return $request->getSchemeAndHttpHost() . $url;
    }

    /**
     * Without the empty values, at every depth: a template passes what it has,
     * nulls included.
     *
     * @param array<mixed> $node
     *
     * @return array<mixed>
     */
    private static function clean(array $node): array
    {
        $list = array_is_list($node);
        foreach ($node as $key => $value) {
            if (is_array($value)) {
                $value = self::clean($value);
                $node[$key] = $value;
            }
            if ($value === null || $value === '' || $value === []) {
                unset($node[$key]);
            }
        }

        return $list ? array_values($node) : $node;
    }
}
