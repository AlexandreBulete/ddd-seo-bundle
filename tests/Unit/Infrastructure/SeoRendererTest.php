<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Tests\Unit\Infrastructure;

use AlexandreBulete\DddSeoBundle\Application\Query\FindSeoHead\SeoHead;
use AlexandreBulete\DddSeoBundle\Domain\Model\SeoSettings;
use AlexandreBulete\DddSeoBundle\Domain\Model\SeoTemplate;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoFields;
use AlexandreBulete\DddSeoBundle\Infrastructure\Config\SeoKinds;
use AlexandreBulete\DddSeoBundle\Infrastructure\Rendering\SeoRenderer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use AlexandreBulete\DddSeoBundle\Infrastructure\Rendering\StructuredData;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * What was set by hand, else the saved template, else the application's
 * default for the kind, else the site's.
 */
final class SeoRendererTest extends TestCase
{
    private const array VARIABLES = ['title' => 'Un livre', 'author' => 'Une autrice', 'site' => 'Le site', 'description' => 'The text of the review.', 'url' => '/reviews/un-livre', 'image' => '/media/cover.jpg'];

    #[Test]
    public function theKindsDefaultWhenNothingIsSaved(): void
    {
        $seo = $this->renderer()->view($this->head(), 'review', self::VARIABLES);

        self::assertSame('Un livre, Une autrice — Le site', $seo->title);
        self::assertSame('The text of the review.', $seo->description);
        self::assertSame('https://site.example/reviews/un-livre', $seo->canonical);
        self::assertSame('https://site.example/media/cover.jpg', $seo->image);
        self::assertSame('book', $seo->type);
        self::assertSame('Un livre', $seo->shareTitle);
        self::assertFalse($seo->noindex);
    }

    #[Test]
    public function aSavedTemplateThenWhatWasSetByHand(): void
    {
        $template = SeoTemplate::defaults('review');
        $template->change('{title}: review — {site}', 'My review of {title}.', new \DateTimeImmutable());

        $seo = $this->renderer()->view($this->head(template: $template), 'review', self::VARIABLES);
        self::assertSame('Un livre: review — Le site', $seo->title);
        self::assertSame('My review of Un livre.', $seo->description);

        $seo = $this->renderer()->view($this->head(new SeoFields('À la main', canonical: 'https://elsewhere.example/a', shareTitle: 'Partagé'), $template), 'review', self::VARIABLES);
        self::assertSame('À la main', $seo->title);
        self::assertSame('https://elsewhere.example/a', $seo->canonical);
        self::assertSame('Partagé', $seo->shareTitle);
    }

    #[Test]
    public function theSitesPatternForAKindWithoutDefault(): void
    {
        $seo = $this->renderer()->view($this->head(), 'route', ['title' => 'Le blog', 'site' => 'Le site']);

        self::assertSame('Le blog — Le site', $seo->title);
        self::assertNull($seo->description);
        self::assertSame('https://site.example/current', $seo->canonical);
    }

    #[Test]
    public function aSiteClosedToEnginesIsNoindexEverywhere(): void
    {
        $settings = SeoSettings::defaults();
        $settings->change(false, null, null, new \DateTimeImmutable());

        self::assertTrue($this->renderer()->view($this->head(settings: $settings), 'review', self::VARIABLES)->noindex);
        self::assertTrue($this->renderer()->view($this->head(new SeoFields(noindex: true)), 'review', self::VARIABLES)->noindex);
        self::assertTrue($this->renderer()->view($this->head(), 'review', ['noindex' => true] + self::VARIABLES)->noindex);
        self::assertSame('noindex, follow', $this->renderer()->view($this->head(settings: $settings), 'review', [])->robots());
    }

    #[Test]
    public function theStructuredDataDescribesTheSiteThePersonAndThePage(): void
    {
        $settings = SeoSettings::defaults();
        $settings->describePerson('Ann Author', '/about', ['https://social.example/ann'], new \DateTimeImmutable());
        $variables = self::VARIABLES + [
            'locale' => 'en_GB',
            'schema' => ['@type' => 'Review', 'name' => 'Un livre', 'itemReviewed' => ['@type' => 'Book', 'name' => 'Un livre']],
            'breadcrumbs' => [['name' => 'Home', 'url' => '/'], ['name' => 'Reviews', 'url' => '/reviews'], ['name' => 'Un livre']],
        ];

        $jsonLd = $this->renderer()->view($this->head(new SeoFields(jsonLd: '{"@context": "https://schema.org", "@type": "Event", "name": "Reading"}'), settings: $settings), 'review', $variables)->jsonLd;
        $graph = self::graph($jsonLd);

        self::assertSame(['Person', 'WebSite', 'Review', 'BreadcrumbList', 'Event'], array_column($graph, '@type'));
        self::assertSame(['https://social.example/ann'], $graph[0]['sameAs']);
        self::assertSame('https://site.example/about', $graph[0]['url']);
        self::assertStringContainsString('"urlTemplate":"https://site.example/search?q={search_term_string}"', (string) $jsonLd);
        self::assertSame('en-GB', $graph[1]['inLanguage']);
        self::assertSame(['@id' => 'https://site.example/#person'], $graph[2]['author']);
        self::assertSame('https://site.example/reviews/un-livre', $graph[2]['url']);
        self::assertStringContainsString('{"@type":"ListItem","position":3,"name":"Un livre","item":"https://site.example/reviews/un-livre"}', (string) $jsonLd);
        self::assertArrayNotHasKey('@context', $graph[4]);
    }

    #[Test]
    public function aKindsSwitchKeepsTheSiteAndWhatWasSetByHand(): void
    {
        $template = SeoTemplate::defaults('review');
        $template->change(null, null, new \DateTimeImmutable(), structuredData: false);
        $variables = self::VARIABLES + ['schema' => ['@type' => 'Review'], 'breadcrumbs' => [['name' => 'Home', 'url' => '/']]];

        $graph = self::graph($this->renderer()->view($this->head(new SeoFields(jsonLd: '[{"@type": "Event"}]'), $template), 'review', $variables)->jsonLd);

        self::assertSame(['WebSite', 'Event'], array_column($graph, '@type'));
    }

    #[Test]
    public function structuredDataSetByHandIsJsonWithTypes(): void
    {
        foreach (['not json', '[]', '{"name": "untyped"}', '["a"]'] as $invalid) {
            try {
                new SeoFields(jsonLd: $invalid);
                self::fail($invalid . ' was accepted.');
            } catch (\InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
        self::assertStringContainsString('"@type": "Event"', (string) (new SeoFields(jsonLd: '{"@type":"Event"}'))->jsonLd);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function graph(?string $jsonLd): array
    {
        self::assertNotNull($jsonLd);
        /** @var array{'@graph': list<array<string, mixed>>} $data */
        $data = json_decode($jsonLd, true, 32, \JSON_THROW_ON_ERROR);

        return $data['@graph'];
    }

    private function renderer(): SeoRenderer
    {
        $requests = new RequestStack();
        $requests->push(Request::create('https://site.example/current?page=2'));
        $kinds = new SeoKinds([
            'review' => ['label' => 'Reviews', 'variables' => [], 'title' => '{title}, {author} — {site}', 'description' => null, 'type' => 'book'],
        ], []);

        $urls = self::createStub(UrlGeneratorInterface::class);
        $urls->method('generate')->willReturn('https://site.example/search?q=SEARCH_TERM_PLACEHOLDER');

        return new SeoRenderer($kinds, $requests, new StructuredData($urls, ['route' => 'search', 'parameter' => 'q']));
    }

    private function head(?SeoFields $fields = null, ?SeoTemplate $template = null, ?SeoSettings $settings = null): SeoHead
    {
        return new SeoHead($fields ?? new SeoFields(), $template ?? SeoTemplate::defaults('review'), $settings ?? SeoSettings::defaults());
    }
}
