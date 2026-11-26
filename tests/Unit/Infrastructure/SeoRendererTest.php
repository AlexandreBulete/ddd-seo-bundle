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

    private function renderer(): SeoRenderer
    {
        $requests = new RequestStack();
        $requests->push(Request::create('https://site.example/current?page=2'));
        $kinds = new SeoKinds([
            'review' => ['label' => 'Reviews', 'variables' => [], 'title' => '{title}, {author} — {site}', 'description' => null, 'type' => 'book'],
        ], []);

        return new SeoRenderer($kinds, $requests);
    }

    private function head(?SeoFields $fields = null, ?SeoTemplate $template = null, ?SeoSettings $settings = null): SeoHead
    {
        return new SeoHead($fields ?? new SeoFields(), $template ?? SeoTemplate::defaults('review'), $settings ?? SeoSettings::defaults());
    }
}
