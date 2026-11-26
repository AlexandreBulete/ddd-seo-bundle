<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle;

use AlexandreBulete\DddSeoBundle\Infrastructure\Doctrine\Type\SeoEntryIdType;
use AlexandreBulete\DddSeoBundle\Infrastructure\Sitemap\SitemapSourceInterface;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Titles, descriptions, canonical addresses, share tags, sitemap and
 * robots.txt, edited in a Sylius back office.
 *
 * The SEO hangs on a page — a kind the application declares and a
 * reference — never on the content itself: the bundle knows no content. The
 * application declares its kinds (`seo.kinds`), its fixed pages
 * (`seo.routes`), the back-office forms that get a SEO block (`seo.forms`)
 * and, for the sitemap, one `SitemapSourceInterface` per kind.
 *
 * @phpstan-type SeoConfig array{
 *     kinds: array<string, array{label: string, variables: array<string, string>, title: ?string, description: ?string, type: string}>,
 *     routes: array<string, string>,
 *     forms: array<class-string, string>,
 *     search: array{route: ?string, parameter: string},
 *     admin: array{enabled: bool},
 * }
 */
final class DddSeoBundle extends AbstractBundle
{
    protected string $extensionAlias = 'seo';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('kinds')
                    ->info('The kinds of pages: review, post… Their reference is the content\'s id.')
                    ->useAttributeAsKey('kind')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('label')->isRequired()->cannotBeEmpty()->info('Translation key or text, for the back office.')->end()
                            ->arrayNode('variables')
                                ->info('What the templates may use: name => what it holds (translation key or text).')
                                ->useAttributeAsKey('name')
                                ->scalarPrototype()->end()
                            ->end()
                            ->scalarNode('title')->defaultNull()->info('Title pattern when none is saved; null: the site\'s.')->end()
                            ->scalarNode('description')->defaultNull()->info('Description pattern when none is saved; null: the content\'s own description.')->end()
                            ->scalarNode('type')->defaultValue('website')->info('Open Graph type.')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('routes')
                    ->info('The fixed pages: route name => label. Their reference is the route name.')
                    ->useAttributeAsKey('route')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('forms')
                    ->info('Back-office forms that get a SEO block: form type => kind.')
                    ->useAttributeAsKey('type')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('search')
                    ->info('The site\'s search, announced to engines in the structured data.')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('route')->defaultNull()->info('Route of the results page; null: none.')->end()
                        ->scalarNode('parameter')->defaultValue('q')->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Sylius back-office screens. Turn off for a headless deployment.')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        /** @var SeoConfig $config */
        $container->parameters()
            ->set('seo.kinds', $config['kinds'])
            ->set('seo.routes', $config['routes'])
            ->set('seo.forms', $config['forms'])
            ->set('seo.search', $config['search']);

        $container->import($this->getPath() . '/config/services.php');

        if ($config['admin']['enabled']) {
            $container->import($this->getPath() . '/config/services_admin.php');
        }
        if (self::hasBundle($builder, DoctrineMigrationsBundle::class)) {
            $container->import($this->getPath() . '/config/services_migrations.php');
        }
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerForAutoconfiguration(SitemapSourceInterface::class)
            ->addTag(SitemapSourceInterface::TAG);
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Before the config tree is processed: defaults are not applied yet.
        $adminEnabled = true;
        /** @var list<array<string, mixed>> $configs */
        $configs = $builder->getExtensionConfig($this->extensionAlias);
        foreach ($configs as $config) {
            $admin = $config['admin'] ?? null;
            if (is_array($admin) && is_bool($admin['enabled'] ?? null)) {
                $adminEnabled = $admin['enabled'];
            }
        }

        $builder->prependExtensionConfig('doctrine', [
            'dbal' => ['types' => [SeoEntryIdType::NAME => SeoEntryIdType::class]],
            'orm' => [
                'mappings' => [
                    'Seo' => [
                        'type' => 'xml',
                        'is_bundle' => false,
                        'dir' => $this->getPath() . '/src/Infrastructure/Doctrine/Mapping',
                        'prefix' => 'AlexandreBulete\DddSeoBundle\Domain',
                        'alias' => 'Seo',
                    ],
                ],
            ],
        ]);

        if (self::hasBundle($builder, DoctrineMigrationsBundle::class)) {
            $builder->prependExtensionConfig('doctrine_migrations', ['enable_service_migrations' => true]);
        }

        $builder->prependExtensionConfig('framework', [
            'translator' => ['paths' => [$this->getPath() . '/translations']],
        ]);

        if ($adminEnabled) {
            // DddSyliusBundle only globs the application's own resources.
            $builder->prependExtensionConfig('sylius_resource', [
                'mapping' => ['paths' => [$this->getPath() . '/src/Infrastructure/Sylius/Resource']],
            ]);
        }
    }

    /**
     * From `kernel.bundles`: inside loadExtension() the builder is scoped to
     * this extension, so hasExtension() would answer false.
     *
     * @param class-string $bundle
     */
    private static function hasBundle(ContainerBuilder $builder, string $bundle): bool
    {
        /** @var array<string, class-string> $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return in_array($bundle, $bundles, true);
    }
}
