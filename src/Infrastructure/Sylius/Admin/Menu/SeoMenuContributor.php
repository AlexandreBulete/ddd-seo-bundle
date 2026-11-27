<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Admin\Menu;

use AlexandreBulete\DddSeoBundle\Domain\Model\SeoSettings;
use AlexandreBulete\DddSyliusBundle\Admin\Menu\MenuContributorInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * "SEO": the fixed pages, the redirects, the templates by kind, the
 * site-wide rules.
 */
#[AutoconfigureTag('app.menu_contributor', ['priority' => 13])]
final readonly class SeoMenuContributor implements MenuContributorInterface
{
    public function __construct(
        private AuthorizationCheckerInterface $authorization,
    ) {}

    public function contribute(ItemInterface $menu): void
    {
        $edit = $this->authorization->isGranted('seo.edit');
        $configure = $this->authorization->isGranted('seo.configure');
        $redirect = $this->authorization->isGranted('seo.redirect');
        if (!$edit && !$configure && !$redirect) {
            return;
        }

        $root = $menu->addChild('seo')
            ->setLabel('seo.menu.root')
            ->setLabelAttribute('icon', 'tabler:world-search');
        if ($edit) {
            $root->addChild('seo_pages', ['route' => 'seo_admin_seo_page_index'])->setLabel('seo.menu.pages');
        }
        if ($redirect) {
            $root->addChild('seo_redirects', ['route' => 'seo_admin_redirect_index'])->setLabel('seo.menu.redirects');
        }
        if ($configure) {
            $root->addChild('seo_templates', ['route' => 'seo_admin_seo_template_index'])->setLabel('seo.menu.templates');
            $root->addChild('seo_settings', ['route' => 'seo_admin_seo_settings_update', 'routeParameters' => ['id' => SeoSettings::KEY]])
                ->setLabel('seo.menu.settings');
        }
    }
}
