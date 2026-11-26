<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Config;

use AlexandreBulete\DddSeoBundle\Domain\Service\SeoKindCatalogInterface;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoTarget;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The kinds of pages, from `seo.kinds`, plus `route`: the application's fixed
 * pages (`seo.routes`), whose reference is the route name.
 *
 * @phpstan-type Kind array{label: string, variables: array<string, string>, title: ?string, description: ?string, type: string}
 */
final readonly class SeoKinds implements SeoKindCatalogInterface
{
    public const string ROUTE = 'route';

    /** @var array<string, Kind> */
    private array $kinds;

    /**
     * @param array<string, Kind>   $kinds
     * @param array<string, string> $routes route name => label
     */
    public function __construct(
        #[Autowire(param: 'seo.kinds')]
        array $kinds,
        #[Autowire(param: 'seo.routes')]
        private array $routes,
    ) {
        foreach (array_keys($kinds) as $kind) {
            new SeoTarget($kind, 'x'); // validates the kind
        }
        $kinds[self::ROUTE] ??= [
            'label' => 'seo.kind.route',
            'variables' => ['title' => 'seo.variable.page_title', 'site' => 'seo.variable.site'],
            'title' => null,
            'description' => null,
            'type' => 'website',
        ];
        $this->kinds = $kinds;
    }

    public function has(string $kind): bool
    {
        return isset($this->kinds[$kind]);
    }

    /**
     * @return list<string>
     */
    public function kinds(): array
    {
        return array_keys($this->kinds);
    }

    public function label(string $kind): string
    {
        return $this->kinds[$kind]['label'] ?? $kind;
    }

    /**
     * @return array<string, string> name => what it holds
     */
    public function variables(string $kind): array
    {
        return $this->kinds[$kind]['variables'] ?? [];
    }

    public function defaultTitle(string $kind): ?string
    {
        return $this->kinds[$kind]['title'] ?? null;
    }

    public function defaultDescription(string $kind): ?string
    {
        return $this->kinds[$kind]['description'] ?? null;
    }

    /**
     * Open Graph type: `website`, `article`, `book`…
     */
    public function type(string $kind): string
    {
        return $this->kinds[$kind]['type'] ?? 'website';
    }

    /**
     * @return array<string, string> route name => label
     */
    public function routes(): array
    {
        return $this->routes;
    }
}
