# ddd-seo-bundle

SEO as a reusable DDD Bounded Context for Symfony: the title, description,
canonical address and share tags of every page, **templates by kind of
content**, **structured data** (JSON-LD), `sitemap.xml` and `robots.txt` — all edited in a Sylius back office
by whoever takes care of the site's search ranking, without a deploy.

The bundle knows no content. SEO hangs on a **page**: a kind the application
declares (`review`, `post`, `route` for its fixed pages…) and a reference (the
content's id, or the route name).

## Installation

```bash
composer require alexandrebulete/ddd-seo-bundle
bin/console doctrine:migrations:migrate
```

The Flex recipe registers the bundle and imports its routes (`/sitemap.xml`,
`/robots.txt`). The `seo_entry`, `seo_template` and `seo_settings` tables come
with the bundle's own migration. Remove any static `public/robots.txt`: it
would be served instead.

## What wins

For each page, field by field:

1. what was **set by hand** on that page (back office);
2. else the **template of its kind**, saved in the back office;
3. else the application's default for the kind (`seo.kinds.<kind>.title`);
4. else the site-wide title pattern (`{title} — {site}` by default) — and, for
   the description, the content's own text, cut between words.

A page is kept out of the engines (`noindex, follow`, out of the sitemap) when
it is set so by hand, when the template passes `noindex: true` (a combination
of filters, a search), or for the whole site while indexing is turned off in
the settings — a test copy, a site not yet live: robots.txt then disallows
everything and the sitemap is empty.

## Configuration

```yaml
seo:
    kinds:
        review:
            label: Reviews
            type: book                          # Open Graph type
            title: '{title}, {author} — {site}' # default pattern; null: the site's
            description: ~                      # null: the content's own text
            variables:                          # listed in the back office
                title: book title
                author: author
                site: site name
    routes:                                     # fixed pages: route => label
        home: Home
        blog: Blog
    forms:                                      # back-office forms that get a SEO block
        App\Library\Infrastructure\Form\BookType: review
    search: { route: search, parameter: q }     # announced in the structured data
    admin:
        enabled: true                           # false for a headless setup
```

The `route` kind is built in: its pages are `seo.routes`, listed on the
back-office screen **SEO → Site pages**.

## In the layout

```twig
<head>
    {{ seo_head(seo_kind|default('route'),
                seo_ref|default(app.request.attributes.get('_route')),
                {site: 'My site', locale: 'en_US'}|merge(seo_vars|default({}))) }}
</head>
```

and in a page:

```twig
{% set seo_kind = 'review' %}
{% set seo_ref = book.id %}
{% set seo_vars = {title: book.title, author: book.author, description: book.summary, image: cover_url} %}
```

`seo_head()` prints the title, description, robots, canonical, Open Graph and
Twitter card tags. `seo_view()` gives the same values for a template that
prints them itself. Variables with a meaning of their own:

| Variable | Role |
|---|---|
| `title`, `description` | the content's own, the last resort — and the share title |
| `url` | canonical address when none was set by hand (else the current one, without its query string) |
| `image` | share image, absolute or a path of this site |
| `noindex` | `true` keeps the page out of the engines |
| `type` | Open Graph type, instead of the kind's |
| `site`, `locale` | site name, `og:locale` |
| `schema` | the page's schema.org node (see below) |
| `breadcrumbs` | its trail: `[{name, url}, …]`, the last one is the page |

Every other variable only fills the templates.

## Structured data

`seo_head()` also prints one JSON-LD `@graph`:

- `WebSite`, with a `SearchAction` when `seo.search` names a route;
- `Person`, when **SEO → Settings** names who writes the site (with the page
  about them and their profiles elsewhere);
- the page's node, from the `schema` variable — built by the template, which
  knows its content:

  ```twig
  schema: {'@type': 'Review', name: book.title, reviewRating: {'@type': 'Rating', ratingValue: 4.5, bestRating: 5},
           itemReviewed: {'@type': 'Book', name: book.title, author: {'@type': 'Person', name: author}}},
  ```

  The bundle adds its `@id`, `url` and `inLanguage`; the site's person as
  `author` and `publisher` of an `Article`, `BlogPosting` or `Review`; the
  site as `isPartOf` of a `…Page`, and the person as `mainEntity` of an
  `AboutPage`. Empty values are dropped, at any depth;
- a `BreadcrumbList`, from `breadcrumbs`;
- what was set by hand on the page (**SEO** block, JSON-LD field): an object
  or a list of objects, each with its `@type`.

Each kind's template has a switch: off, the page's node and trail are left
out; the site and what was set by hand stay.

## The SEO block of a content

Each form listed in `seo.forms` gets a **SEO** block once the content exists
(its id is the reference): title, description, canonical, noindex, share title
and text, structured data. All empty: the page follows its template again. The form's data
must expose the id, as `getId()` or a property `id` (a `Uid` is read in its
RFC 4122 form, as an `IdentifierVO` prints itself).

## The sitemap

One `SitemapSourceInterface` per kind, in the application — only what a
visitor can see:

```php
final readonly class ReviewSitemapSource implements SitemapSourceInterface
{
    public function kind(): string { return 'review'; }

    public function entries(): iterable
    {
        foreach ($this->publishedReviews() as $book) {
            yield new SitemapEntry((string) $book->id, $this->urls->generate('review', ['slug' => $book->slug]), $book->updatedAt);
        }
    }
}
```

Autoconfigured. The fixed pages come first; a page set to noindex, or whose
canonical points elsewhere, is left out.

## Use cases

Commands: `SaveSeoEntry`, `ChangeSeoTemplate`, `ChangeSeoSettings`.
Queries: `FindSeoHead` and `FindSitemapExclusions` (`#[PublicAccess]`),
`FindSeoEntry`, `FindSeoEntries`, `FindSeoTemplate`, `FindSeoSettings`.

Permissions: `seo.edit` (a page's SEO, the fixed pages) and `seo.configure`
(templates, settings).

## Seams

Ports bound in `config/services.php`, to rebind in the application:

| Port | Default | Rebind for |
|---|---|---|
| `DomainEventPublisherInterface` | `ImmediateEventPublisher` (in-process, in the transaction) | an outbox |
| `SeoEntryRepositoryInterface` | Doctrine | another storage |
| `SeoTemplateRepositoryInterface` | Doctrine | another storage |
| `SeoSettingsRepositoryInterface` | Doctrine | another storage |

## Development

```bash
composer qa    # phpstan (max, strict) + deptrac + phpunit
```

Integration tests run on SQLite in memory, or on the database given by
`DDD_TEST_DATABASE_URL` (CI: PostgreSQL, MySQL, SQLite).

## License

MIT
