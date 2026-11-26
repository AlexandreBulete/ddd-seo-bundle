<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Rendering;

use AlexandreBulete\DddSeoBundle\Application\Query\FindSeoHead\SeoHead;
use AlexandreBulete\DddSeoBundle\Infrastructure\Config\SeoKinds;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Resolves a page's head: what was set by hand, else the kind's template
 * (saved in the back office, else the application's default), else the
 * site's. The application's variables fill the patterns; the well-known ones
 * also feed what a pattern does not cover:
 *
 * - `title`, `description`: the content's own, the last resort;
 * - `url`: the canonical address when none was set (else the current one,
 *   without its query string);
 * - `image`: the share image; `noindex`: true keeps the page out;
 * - `site`, `locale`: the site's name and language (`fr_FR`).
 */
final readonly class SeoRenderer
{
    public const int DESCRIPTION_MAX_LENGTH = 160;

    public function __construct(
        private SeoKinds $kinds,
        private RequestStack $requests,
    ) {}

    /**
     * @param array<string, mixed> $variables
     */
    public function view(SeoHead $head, string $kind, array $variables): SeoView
    {
        $fields = $head->fields;
        $own = static fn (string $name): ?string => self::text($variables[$name] ?? null);

        $title = $fields->title
            ?? Pattern::fill($head->template->title ?? $this->kinds->defaultTitle($kind) ?? $head->settings->titlePattern, $variables);
        if ($title === '') {
            $title = $own('site') ?? '';
        }

        $description = $fields->description;
        if ($description === null) {
            $pattern = $head->template->description ?? $this->kinds->defaultDescription($kind);
            $description = $pattern !== null ? Pattern::fill($pattern, $variables) : ($own('description') ?? '');
            $description = $description === '' ? null : Pattern::shorten($description, self::DESCRIPTION_MAX_LENGTH);
        }

        return new SeoView(
            title: $title,
            description: $description,
            canonical: $this->absolute($fields->canonical ?? $own('url')) ?? $this->current(),
            noindex: !$head->settings->indexing || $fields->noindex || ($variables['noindex'] ?? false) === true,
            shareTitle: $fields->shareTitle ?? $own('title') ?? $title,
            shareDescription: $fields->shareDescription ?? $description,
            image: $this->absolute($own('image')),
            type: $own('type') ?? $this->kinds->type($kind),
            siteName: $own('site'),
            locale: $own('locale'),
        );
    }

    private static function text(mixed $value): ?string
    {
        if (!is_scalar($value) && !$value instanceof \Stringable) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function absolute(?string $url): ?string
    {
        if ($url === null || preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }
        $request = $this->requests->getMainRequest();

        return $request !== null && str_starts_with($url, '/') ? $request->getSchemeAndHttpHost() . $url : $url;
    }

    private function current(): ?string
    {
        $request = $this->requests->getMainRequest();

        return $request === null ? null : $request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo();
    }
}
