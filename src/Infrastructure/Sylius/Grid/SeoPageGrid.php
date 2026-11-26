<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddSeoBundle\Infrastructure\Sylius\Resource\SeoPageResource;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Bundle\GridBundle\Grid\AbstractGrid;
use Sylius\Bundle\GridBundle\Grid\ResourceAwareGridInterface;
use Sylius\Component\Grid\Builder\GridBuilderInterface;

class SeoPageGrid extends AbstractGrid implements ResourceAwareGridInterface
{
    public static function getName(): string
    {
        return self::class;
    }

    public function buildGrid(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(SeoPageGridProvider::class)
            ->addField(StringField::create('label')->setLabel('seo.page.label'))
            ->addField(StringField::create('path')->setLabel('seo.page.path'))
            ->addField(StringField::create('title')->setLabel('seo.field.title')->setPath('titleOrDash'))
            ->addActionGroup(ItemActionGroup::create(UpdateAction::create()))
        ;
    }

    public function getResourceClass(): string
    {
        return SeoPageResource::class;
    }
}
