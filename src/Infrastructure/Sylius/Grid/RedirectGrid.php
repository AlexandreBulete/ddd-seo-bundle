<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\RedirectResource;
use Sylius\Bundle\GridBundle\Builder\Action\CreateAction;
use Sylius\Bundle\GridBundle\Builder\Action\DeleteAction;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\BulkActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\MainActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\DateTimeField;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Builder\Field\TwigField;
use Sylius\Bundle\GridBundle\Builder\Filter\StringFilter;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;
use Sylius\Component\Grid\Builder\GridBuilderInterface;

class RedirectGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    public static function getName(): string
    {
        return self::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(RedirectGridProvider::class)
            ->setLimits([50, 100, 200])
            ->addFilter(StringFilter::create('search')->setLabel('seo.redirect.search'))
            ->addField(StringField::create('source')->setLabel('seo.redirect.source'))
            ->addField(StringField::create('target')->setLabel('seo.redirect.target'))
            ->addField(TwigField::create('origin', '@DddSeo/admin/grid/field/translated.html.twig')->setLabel('seo.redirect.origin')->setPath('originLabel'))
            ->addField(StringField::create('hits')->setLabel('seo.redirect.hits'))
            ->addField(DateTimeField::create('lastHitAt')->setLabel('seo.redirect.last_hit_at'))
            ->addActionGroup(MainActionGroup::create(CreateAction::create()))
            ->addActionGroup(ItemActionGroup::create(UpdateAction::create(), DeleteAction::create()))
            ->addActionGroup(BulkActionGroup::create(DeleteAction::create()))
        ;
    }

    public function getResourceClass(): string
    {
        return RedirectResource::class;
    }
}
